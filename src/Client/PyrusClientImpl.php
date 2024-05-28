<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Client;

use SuareSu\PyrusClient\Exception\PyrusApiException;
use SuareSu\PyrusClient\Exception\PyrusApiUnauthorizedException;
use SuareSu\PyrusClient\Exception\PyrusDataConverterException;
use SuareSu\PyrusClient\Exception\PyrusTransportException;
use SuareSu\PyrusClient\Pyrus\PyrusEndpoint;
use SuareSu\PyrusClient\Pyrus\PyrusHeader;
use SuareSu\PyrusClient\Transport\PyrusRequest;
use SuareSu\PyrusClient\Transport\PyrusRequestMethod;
use SuareSu\PyrusClient\Transport\PyrusResponseStatus;
use SuareSu\PyrusClient\Transport\PyrusTransport;

/**
 * Basic implementation for PyrusClient interface.
 *
 * @psalm-api
 */
final class PyrusClientImpl implements PyrusClient
{
    private ?PyrusAuthToken $token = null;

    private ?PyrusCredentials $credentials = null;

    private bool $isAuthTokenRefreshing = false;

    public function __construct(
        private readonly PyrusTransport $transport,
        private readonly PyrusClientOptions $options
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function useAuthToken(PyrusAuthToken $token): void
    {
        $this->token = $token;
    }

    /**
     * {@inheritdoc}
     */
    public function useAuthCredentials(PyrusCredentials $credentials): void
    {
        $this->token = null;
        $this->credentials = $credentials;
    }

    /**
     * {@inheritdoc}
     */
    public function auth(PyrusCredentials $credentials): PyrusAuthToken
    {
        $method = PyrusEndpoint::AUTH->method();
        $url = $this->createEndpointUrl(
            endpoint: PyrusEndpoint::AUTH,
            forceBaseUrl: $this->options->accountsBaseUrl
        );
        $payload = [
            'login' => $credentials->login,
            'security_key' => $credentials->securityKey,
        ];
        if (null !== $credentials->personId) {
            $payload['person_id'] = $credentials->personId;
        }

        $response = $this->requestInternal($method, $url, $payload);

        return new PyrusAuthToken(
            (string) ($response['access_token'] ?? ''),
            (string) ($response['api_url'] ?? ''),
            (string) ($response['files_url'] ?? ''),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function request(PyrusEndpoint $endpoint, array $urlParams = [], ?array $payload = null): array
    {
        $authToken = $this->getOrRequestAuthorizationToken()->accessToken;
        $method = $endpoint->method();
        $url = $this->createEndpointUrl($endpoint, $urlParams);
        $headers = [
            PyrusHeader::AUTHORIZATION->value => "Bearer {$authToken}",
        ];

        try {
            $response = $this->requestInternal($method, $url, $payload, $headers);
        } catch (PyrusApiUnauthorizedException $e) {
            if ($this->isAuthTokenRefreshing) {
                throw $e;
            } else {
                $this->token = null;
                $this->isAuthTokenRefreshing = true;
                $response = $this->request($endpoint, $urlParams, $payload);
                $this->isAuthTokenRefreshing = false;
            }
        }

        return $response;
    }

    /**
     * Return token if it presents. In other case tries to request it.
     */
    private function getOrRequestAuthorizationToken(): PyrusAuthToken
    {
        if (null !== $this->token) {
            return $this->token;
        }

        if (null === $this->credentials) {
            throw new PyrusApiException('Please provide credentials or authorization token');
        }

        $this->token = $this->auth($this->credentials);

        return $this->token;
    }

    /**
     * Create and run a request to Pyrus using set data.
     *
     * @param array<string, mixed>|null      $payload
     * @param array<string, string|string[]> $headers
     *
     * @psalm-param non-empty-string $url
     *
     * @return array<string, mixed>
     *
     * @throws PyrusTransportException
     * @throws PyrusApiException
     * @throws PyrusDataConverterException
     */
    private function requestInternal(PyrusRequestMethod $method, string $url, ?array $payload = null, array $headers = []): array
    {
        $headers[PyrusHeader::CONTENT_TYPE->value] = 'application/json';

        try {
            $request = new PyrusRequest($method, $url, $payload, $headers);
            $response = $this->transport->request($request);
        } catch (\Throwable $e) {
            throw new PyrusTransportException(message: $e->getMessage(), previous: $e);
        }

        $parsedResponse = $this->parseResponse($response->payload);

        if (!empty($parsedResponse['error'])) {
            throw new PyrusApiException(
                (string) $parsedResponse['error'],
                (int) ($parsedResponse['error_code'] ?? 0)
            );
        }

        if (PyrusResponseStatus::UNAUTHORIZED === $response->status) {
            throw new PyrusApiUnauthorizedException();
        }

        if (PyrusResponseStatus::OK !== $response->status) {
            throw new PyrusTransportException("Bad response status: {$response->status->value}");
        }

        return $parsedResponse;
    }

    /**
     * Create an absolute URL for provided Pyrus endpoint.
     *
     * @param array<float|int|string> $urlParams
     *
     * @psalm-return non-empty-string
     */
    private function createEndpointUrl(PyrusEndpoint $endpoint, array $urlParams = [], ?string $forceBaseUrl = null): string
    {
        $baseUrl = (string) $this->token?->apiUrl;
        if (null !== $forceBaseUrl) {
            $baseUrl = $forceBaseUrl;
        }

        $path = $endpoint->path($urlParams);

        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Parse response string to an associative array.
     *
     * @return array<string, mixed>
     */
    private function parseResponse(string $payload): array
    {
        try {
            /** @var array<string, mixed> */
            $parsedResponse = json_decode(json: $payload, associative: true, flags: \JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            throw new PyrusTransportException("Can't convert response payload to an array");
        }

        return $parsedResponse;
    }
}
