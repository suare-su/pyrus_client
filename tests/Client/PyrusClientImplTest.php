<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Tests\Client;

use SuareSu\PyrusClient\Client\PyrusAuthToken;
use SuareSu\PyrusClient\Client\PyrusClientImpl;
use SuareSu\PyrusClient\Client\PyrusClientOptions;
use SuareSu\PyrusClient\Client\PyrusCredentials;
use SuareSu\PyrusClient\DataConverter\PyrusDataConverter;
use SuareSu\PyrusClient\Exception\PyrusApiException;
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
        $credentials = new PyrusCredentials('test', 'test', 'test');
        $normalizedCredentials = [
            'test' => 'normalized_credentials',
        ];

        $authTokenJson = 'auth_token_json';
        $authTokenArray = [
            'test' => 'auth_token_json',
        ];
        $authToken = new PyrusAuthToken('test', 'test', 'test');

        $transport = $this->mock(PyrusTransport::class);
        $transport->expects($this->once())
            ->method('request')
            ->with(
                $this->callback(
                    fn (PyrusRequest $request): bool => $request->method === PyrusEndpoint::AUTH->method()
                        && $request->url === PyrusBaseUrl::ACCOUNTS->value . PyrusEndpoint::AUTH->path()
                        && $request->payload === $normalizedCredentials
                        && $request->headers === [
                            PyrusHeader::CONTENT_TYPE->value => 'application/json',
                        ]
                )
            )
            ->willReturn(
                new PyrusResponse(PyrusResponseStatus::OK, $authTokenJson)
            );

        $dataConverter = $this->mock(PyrusDataConverter::class);
        $dataConverter->expects($this->once())
            ->method('normalize')
            ->with(
                $this->identicalTo($credentials)
            )
            ->willReturn($normalizedCredentials);
        $dataConverter->expects($this->once())
            ->method('jsonDecode')
            ->with(
                $this->identicalTo($authTokenJson)
            )
            ->willReturn($authTokenArray);
        $dataConverter->expects($this->once())
            ->method('denormalize')
            ->with(
                $this->identicalTo($authTokenArray),
                $this->identicalTo(PyrusAuthToken::class)
            )
            ->willReturn($authToken);

        $client = new PyrusClientImpl(
            $transport,
            $dataConverter,
            new PyrusClientOptions()
        );
        $res = $client->auth($credentials);

        $this->assertSame($authToken, $res);
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
        $normalizedPayload = [
            'test' => 'payload normalized',
        ];
        $responseString = 'qwe';
        $response = [
            'test' => 'response',
            'test_1' => 'response 1',
        ];

        $authToken = new PyrusAuthToken('test', 'https://test.api/', 'test');

        $transport = $this->mock(PyrusTransport::class);
        $transport->expects($this->once())
            ->method('request')
            ->with(
                $this->callback(
                    fn (PyrusRequest $request): bool => $request->method === $endpoint->method()
                        && $request->url === rtrim($authToken->apiUrl, '/') . $endpoint->path($urlParams)
                        && $request->payload === $normalizedPayload
                        && $request->headers === [
                            PyrusHeader::AUTHORIZATION->value => "Bearer {$authToken->accessToken}",
                            PyrusHeader::CONTENT_TYPE->value => 'application/json',
                        ]
                )
            )
            ->willReturn(
                new PyrusResponse(PyrusResponseStatus::OK, $responseString)
            );

        $dataConverter = $this->mock(PyrusDataConverter::class);
        $dataConverter->expects($this->once())
            ->method('normalize')
            ->with(
                $this->identicalTo($payload)
            )
            ->willReturn($normalizedPayload);
        $dataConverter->expects($this->once())
            ->method('jsonDecode')
            ->with(
                $this->identicalTo($responseString)
            )
            ->willReturn($response);

        $client = new PyrusClientImpl(
            $transport,
            $dataConverter,
            new PyrusClientOptions()
        );
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

        $tokenString = 'token string';
        $token = [
            'test' => 'response',
        ];

        $responseString = 'response string';
        $response = [
            'test' => 'response',
        ];

        $credentials = new PyrusCredentials('login', 'securityKey');
        $authToken = new PyrusAuthToken('old token', 'https://test.api', 'test');
        $refreshedAuthToken = new PyrusAuthToken('new token', 'https://test.api', 'test');

        $transport = $this->mock(PyrusTransport::class);
        $transport->expects($this->exactly(3))
            ->method('request')
            ->willReturnCallback(
                function (PyrusRequest $request) use ($endpoint, $authToken, $refreshedAuthToken, $tokenString, $responseString): PyrusResponse {
                    if (
                        str_ends_with($request->url, $endpoint->path())
                        && isset($request->headers[PyrusHeader::AUTHORIZATION->value])
                    ) {
                        $authHeader = $request->headers[PyrusHeader::AUTHORIZATION->value];
                        if ($authHeader === "Bearer {$authToken->accessToken}") {
                            return new PyrusResponse(PyrusResponseStatus::UNAUTHORIZED, '');
                        } elseif ($authHeader === "Bearer {$refreshedAuthToken->accessToken}") {
                            return new PyrusResponse(PyrusResponseStatus::OK, $responseString);
                        }
                    }
                    if (str_ends_with($request->url, PyrusEndpoint::AUTH->path())) {
                        return new PyrusResponse(PyrusResponseStatus::OK, $tokenString);
                    }

                    return new PyrusResponse(PyrusResponseStatus::SERVER_ERROR, '');
                }
            );

        $dataConverter = $this->mock(PyrusDataConverter::class);
        $dataConverter->expects($this->once())
            ->method('normalize')
            ->with(
                $this->identicalTo($credentials)
            )
            ->willReturn([]);
        $dataConverter->expects($this->once())
            ->method('denormalize')
            ->with(
                $this->identicalTo($token),
                $this->identicalTo(PyrusAuthToken::class)
            )
            ->willReturn($refreshedAuthToken);
        $dataConverter->expects($this->exactly(3))
            ->method('jsonDecode')
            ->willReturnCallback(
                fn (string $payload): array => match ($payload) {
                    $tokenString => $token,
                    $responseString => $response,
                    default => [],
                }
            );

        $client = new PyrusClientImpl(
            $transport,
            $dataConverter,
            new PyrusClientOptions()
        );
        $client->useAuthCredentials($credentials);
        $client->useAuthToken($authToken);
        $res = $client->request($endpoint);

        $this->assertSame($response, $res);
    }

    /**
     * @test
     */
    public function testRequestTransportException(): void
    {
        $endpoint = PyrusEndpoint::CATALOG_INDEX;
        $exceptionMessage = 'test exception';

        $authToken = new PyrusAuthToken('test', 'https://test.api', 'test');

        $transport = $this->mock(PyrusTransport::class);
        $transport->expects($this->once())
            ->method('request')
            ->willThrowException(
                new \RuntimeException($exceptionMessage)
            );

        $dataConverter = $this->mock(PyrusDataConverter::class);

        $client = new PyrusClientImpl(
            $transport,
            $dataConverter,
            new PyrusClientOptions()
        );
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
        $responseString = 'qwe';
        $response = [
            'error' => 'api error',
            'error_code' => 123,
        ];

        $authToken = new PyrusAuthToken('test', 'https://test.api', 'test');

        $transport = $this->mock(PyrusTransport::class);
        $transport->expects($this->once())
            ->method('request')
            ->willReturn(
                new PyrusResponse(PyrusResponseStatus::OK, $responseString)
            );

        $dataConverter = $this->mock(PyrusDataConverter::class);
        $dataConverter->expects($this->once())
            ->method('jsonDecode')
            ->with(
                $this->identicalTo($responseString)
            )
            ->willReturn($response);

        $client = new PyrusClientImpl(
            $transport,
            $dataConverter,
            new PyrusClientOptions()
        );
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
        $responseString = 'qwe';
        $response = [
            'error' => 'api error',
        ];

        $authToken = new PyrusAuthToken('test', 'https://test.api', 'test');

        $transport = $this->mock(PyrusTransport::class);
        $transport->expects($this->once())
            ->method('request')
            ->willReturn(
                new PyrusResponse(PyrusResponseStatus::OK, $responseString)
            );

        $dataConverter = $this->mock(PyrusDataConverter::class);
        $dataConverter->expects($this->once())
            ->method('jsonDecode')
            ->with(
                $this->identicalTo($responseString)
            )
            ->willReturn($response);

        $client = new PyrusClientImpl(
            $transport,
            $dataConverter,
            new PyrusClientOptions()
        );
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
        $responseString = 'qwe';
        $response = [];

        $authToken = new PyrusAuthToken('test', 'https://test.api', 'test');

        $transport = $this->mock(PyrusTransport::class);
        $transport->expects($this->once())
            ->method('request')
            ->willReturn(
                new PyrusResponse(PyrusResponseStatus::SERVER_ERROR, $responseString)
            );

        $dataConverter = $this->mock(PyrusDataConverter::class);
        $dataConverter->expects($this->once())
            ->method('jsonDecode')
            ->with(
                $this->identicalTo($responseString)
            )
            ->willReturn($response);

        $client = new PyrusClientImpl(
            $transport,
            $dataConverter,
            new PyrusClientOptions()
        );
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
        $dataConverter = $this->mock(PyrusDataConverter::class);

        $client = new PyrusClientImpl(
            $transport,
            $dataConverter,
            new PyrusClientOptions()
        );

        $this->expectException(PyrusApiException::class);
        $this->expectExceptionMessage('Please provide credentials or authorization token');
        $client->request($endpoint);
    }
}