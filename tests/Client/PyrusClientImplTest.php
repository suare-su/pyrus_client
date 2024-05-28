<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Tests\Client;

use SuareSu\PyrusClient\Client\PyrusAuthToken;
use SuareSu\PyrusClient\Client\PyrusClientImpl;
use SuareSu\PyrusClient\Client\PyrusClientOptions;
use SuareSu\PyrusClient\Client\PyrusCredentials;
use SuareSu\PyrusClient\Exception\PyrusApiException;
use SuareSu\PyrusClient\Exception\PyrusApiUnauthorizedException;
use SuareSu\PyrusClient\Exception\PyrusTransportException;
use SuareSu\PyrusClient\Pyrus\PyrusBaseUrl;
use SuareSu\PyrusClient\Pyrus\PyrusEndpoint;
use SuareSu\PyrusClient\Pyrus\PyrusHeader;
use SuareSu\PyrusClient\Tests\BaseCase;
use SuareSu\PyrusClient\Transport\PyrusRequest;
use SuareSu\PyrusClient\Transport\PyrusResponse;
use SuareSu\PyrusClient\Transport\PyrusResponseStatus;
use SuareSu\PyrusClient\Transport\PyrusTransport;

/**
 * @internal
 */
final class PyrusClientImplTest extends BaseCase
{
    /**
     * @test
     */
    public function testAuth(): void
    {
        $credentials = $this->createCredentials();
        $response = [
            'access_token' => 'access_token',
            'api_url' => 'api_url',
            'files_url' => 'files_url',
        ];

        $transport = $this->mock(PyrusTransport::class);
        $transport->expects($this->once())
            ->method('request')
            ->with(
                $this->callback(
                    fn (PyrusRequest $request): bool => $request->method === PyrusEndpoint::AUTH->method()
                        && $request->url === PyrusBaseUrl::ACCOUNTS->value . PyrusEndpoint::AUTH->path()
                        && $request->payload === [
                            'login' => $credentials->login,
                            'security_key' => $credentials->securityKey,
                            'person_id' => $credentials->personId,
                        ]
                        && $request->headers === [
                            PyrusHeader::CONTENT_TYPE->value => 'application/json',
                        ]
                )
            )
            ->willReturn(
                $this->createPyrusResponse($response)
            );

        $client = new PyrusClientImpl($transport, $this->createOptions());
        $authToken = $client->auth($credentials);

        $this->assertSame($response['access_token'], $authToken->accessToken);
        $this->assertSame($response['api_url'], $authToken->apiUrl);
        $this->assertSame($response['files_url'], $authToken->filesUrl);
    }

    /**
     * @test
     */
    public function testRequest(): void
    {
        $endpoint = PyrusEndpoint::CATALOG_UPDATE;
        $urlParams = [
            123,
        ];
        $payload = [
            'test' => 'payload',
        ];
        $response = [
            'test' => 'response',
            'test_1' => 'response 1',
        ];

        $authToken = $this->createAuthToken();

        $options = $this->createOptions();

        $transport = $this->mock(PyrusTransport::class);
        $transport->expects($this->once())
            ->method('request')
            ->with(
                $this->callback(
                    fn (PyrusRequest $request): bool => $request->method === $endpoint->method()
                        && $request->url === rtrim($authToken->apiUrl, '/') . $endpoint->path($urlParams)
                        && $request->payload === $payload
                        && $request->headers === [
                            PyrusHeader::AUTHORIZATION->value => "Bearer {$authToken->accessToken}",
                            PyrusHeader::CONTENT_TYPE->value => 'application/json',
                        ]
                ),
                $this->identicalTo($options)
            )
            ->willReturn(
                $this->createPyrusResponse($response)
            );

        $client = new PyrusClientImpl($transport, $options);
        $client->useAuthToken($authToken);
        $res = $client->request($endpoint, $urlParams, $payload);

        $this->assertSame($response, $res);
    }

    /**
     * @test
     */
    public function testRequestWithTokenRefresh(): void
    {
        $endpoint = PyrusEndpoint::CATALOG_INDEX;
        $response = [
            'test' => 'response',
            'test_1' => 'response 1',
        ];
        $tokenResponse = [
            'access_token' => 'access_token_1',
            'api_url' => 'api_url_1',
            'files_url' => 'files_url_1',
        ];
        $credentials = $this->createCredentials();
        $authToken = $this->createAuthToken();

        $transport = $this->mock(PyrusTransport::class);
        $transport->expects($this->exactly(6))
            ->method('request')
            ->willReturnCallback(
                function (PyrusRequest $request) use ($endpoint, $authToken, $response, $tokenResponse): PyrusResponse {
                    if (str_ends_with($request->url, $endpoint->path())) {
                        $authHeader = $request->headers[PyrusHeader::AUTHORIZATION->value] ?? null;
                        if ($authHeader === "Bearer {$authToken->accessToken}") {
                            return $this->createPyrusResponse(status: PyrusResponseStatus::UNAUTHORIZED);
                        } elseif ($authHeader === "Bearer {$tokenResponse['access_token']}") {
                            return $this->createPyrusResponse($response);
                        }
                    }

                    if (str_ends_with($request->url, PyrusEndpoint::AUTH->path())) {
                        return $this->createPyrusResponse($tokenResponse);
                    }

                    return $this->createPyrusResponse(status: PyrusResponseStatus::SERVER_ERROR);
                }
            );

        $client = new PyrusClientImpl($transport, $this->createOptions());
        $client->useAuthCredentials($credentials);
        $client->useAuthToken($authToken);
        $res = $client->request($endpoint);
        $client->useAuthToken($authToken);
        $res1 = $client->request($endpoint);

        $this->assertSame($response, $res, 'Token was successfully refreshed');
        $this->assertSame($response, $res1, 'Object is able to refresh token again');
    }

    /**
     * @test
     */
    public function testRequestWithTokenRefreshAuthorizationIsNotAllowed(): void
    {
        $endpoint = PyrusEndpoint::CATALOG_INDEX;
        $tokenResponse = [
            'access_token' => 'access_token_1',
            'api_url' => 'api_url_1',
            'files_url' => 'files_url_1',
        ];
        $credentials = $this->createCredentials();
        $authToken = $this->createAuthToken();

        $transport = $this->mock(PyrusTransport::class);
        $transport->expects($this->exactly(3))
            ->method('request')
            ->willReturnCallback(
                function (PyrusRequest $request) use ($endpoint, $tokenResponse): PyrusResponse {
                    if (str_ends_with($request->url, $endpoint->path())) {
                        return $this->createPyrusResponse(status: PyrusResponseStatus::UNAUTHORIZED);
                    } elseif (str_ends_with($request->url, PyrusEndpoint::AUTH->path())) {
                        return $this->createPyrusResponse($tokenResponse);
                    } else {
                        return $this->createPyrusResponse(status: PyrusResponseStatus::SERVER_ERROR);
                    }
                }
            );

        $client = new PyrusClientImpl($transport, $this->createOptions());
        $client->useAuthCredentials($credentials);
        $client->useAuthToken($authToken);

        $this->expectException(PyrusApiUnauthorizedException::class);
        $client->request($endpoint);
    }

    /**
     * @test
     */
    public function testRequestTransportException(): void
    {
        $endpoint = PyrusEndpoint::CATALOG_INDEX;
        $exceptionMessage = 'test exception';
        $authToken = $this->createAuthToken();

        $transport = $this->mock(PyrusTransport::class);
        $transport->expects($this->once())
            ->method('request')
            ->willThrowException(
                new \RuntimeException($exceptionMessage)
            );

        $client = new PyrusClientImpl($transport, $this->createOptions());
        $client->useAuthToken($authToken);

        $this->expectException(PyrusTransportException::class);
        $this->expectExceptionMessage($exceptionMessage);
        $client->request($endpoint);
    }

    /**
     * @test
     */
    public function testRequestApiException(): void
    {
        $endpoint = PyrusEndpoint::CATALOG_INDEX;
        $response = [
            'error' => 'api error',
            'error_code' => 123,
        ];
        $authToken = $this->createAuthToken();

        $transport = $this->mock(PyrusTransport::class);
        $transport->expects($this->once())
            ->method('request')
            ->willReturn(
                $this->createPyrusResponse($response)
            );

        $client = new PyrusClientImpl($transport, $this->createOptions());
        $client->useAuthToken($authToken);

        $this->expectException(PyrusApiException::class);
        $this->expectExceptionMessage($response['error']);
        $this->expectExceptionCode($response['error_code']);
        $client->request($endpoint);
    }

    /**
     * @test
     */
    public function testRequestApiExceptionNoErrorCode(): void
    {
        $endpoint = PyrusEndpoint::CATALOG_INDEX;
        $response = [
            'error' => 'api error',
        ];
        $authToken = $this->createAuthToken();

        $transport = $this->mock(PyrusTransport::class);
        $transport->expects($this->once())
            ->method('request')
            ->willReturn(
                $this->createPyrusResponse($response)
            );

        $client = new PyrusClientImpl($transport, $this->createOptions());
        $client->useAuthToken($authToken);

        $this->expectException(PyrusApiException::class);
        $this->expectExceptionMessage($response['error']);
        $this->expectExceptionCode(0);
        $client->request($endpoint);
    }

    /**
     * @test
     */
    public function testRequestBadResponseStatusException(): void
    {
        $endpoint = PyrusEndpoint::CATALOG_INDEX;
        $authToken = $this->createAuthToken();

        $transport = $this->mock(PyrusTransport::class);
        $transport->expects($this->once())
            ->method('request')
            ->willReturn(
                $this->createPyrusResponse(status: PyrusResponseStatus::SERVER_ERROR)
            );

        $client = new PyrusClientImpl($transport, $this->createOptions());
        $client->useAuthToken($authToken);

        $this->expectException(PyrusTransportException::class);
        $this->expectExceptionMessage((string) PyrusResponseStatus::SERVER_ERROR->value);
        $client->request($endpoint);
    }

    /**
     * @test
     */
    public function testRequestNoCredentialsException(): void
    {
        $endpoint = PyrusEndpoint::CATALOG_INDEX;
        $transport = $this->mock(PyrusTransport::class);

        $client = new PyrusClientImpl($transport, $this->createOptions());

        $this->expectException(PyrusApiException::class);
        $this->expectExceptionMessage('Please provide credentials or authorization token');
        $client->request($endpoint);
    }

    /**
     * Create credentials mock to use in tests.
     */
    private function createCredentials(): PyrusCredentials
    {
        return new PyrusCredentials(
            'login',
            'security_key',
            'person_id'
        );
    }

    /**
     * Create auth token mock to use in tests.
     */
    private function createAuthToken(): PyrusAuthToken
    {
        return new PyrusAuthToken(
            'acces_token',
            'https://test.api/',
            'https://test.files/'
        );
    }

    /**
     * Create client options mock to use in tests.
     */
    private function createOptions(): PyrusClientOptions
    {
        return new PyrusClientOptions();
    }

    /**
     * Create pyrus response mock using provided content and status.
     */
    private function createPyrusResponse(array $payload = [], PyrusResponseStatus $status = PyrusResponseStatus::OK): PyrusResponse
    {
        return new PyrusResponse(
            $status,
            json_encode($payload)
        );
    }
}
