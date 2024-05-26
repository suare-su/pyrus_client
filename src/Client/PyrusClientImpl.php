<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Client;

use SuareSu\PyrusClient\Exception\PyrusApiException;
use SuareSu\PyrusClient\Exception\PyrusTransportException;
use SuareSu\PyrusClient\Pyrus\PyrusEndpoint;
use SuareSu\PyrusClient\Transport\PyrusRequest;
use SuareSu\PyrusClient\Transport\PyrusRequestMethod;
use SuareSu\PyrusClient\Transport\PyrusResponse;
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
        $url = $this->createEndpointUrl(endpoint: PyrusEndpoint::AUTH, forceBaseUrl: $this->options->accountsBaseUrl);
        $payload = [
            'login' => $credentials->login,
            'security_key' => $credentials->securityKey,
        ];
        if (null !== $credentials->personId) {
            $payload['person_id'] = $credentials->personId;
        }

        $response = $this->requestInternal($method, $url, $payload);
        /** @psalm-var non-empty-string */
        $accessToken = !empty($response['access_token']) && \is_string($response['access_token']) ? $response['access_token'] : '-';
        /** @psalm-var non-empty-string */
        $apiUrl = !empty($response['api_url']) && \is_string($response['api_url']) ? $response['api_url'] : '-';
        /** @psalm-var non-empty-string */
        $filesUrl = !empty($response['files_url']) && \is_string($response['files_url']) ? $response['files_url'] : '-';

        return new PyrusAuthToken($accessToken, $apiUrl, $filesUrl);
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
     * @param array<string, mixed>           $payload
     * @param array<string, string|string[]> $headers
     *
     * @psalm-param non-empty-string $url
     *
     * @return array<string, mixed>
     *
     * @throws PyrusTransportException
     * @throws PyrusApiException
     */
    private function requestInternal(PyrusRequestMethod $method, string $url, array $payload = [], array $headers = []): array
    {
        try {
            $request = new PyrusRequest($method, $url, $payload, $headers);
            $response = $this->transport->request($request);
            $parsedResponse = $this->parseResponse($response);
        } catch (PyrusTransportException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new PyrusTransportException($e->getMessage(), 0, $e);
        }

        if (!empty($parsedResponse['error'])) {
            throw new PyrusApiException(
                (string) $parsedResponse['error'],
                (int) ($parsedResponse['error_code'] ?? 0)
            );
        }

        if (PyrusResponseStatus::OK !== $response->status) {
            throw new PyrusTransportException("Bad response status: {$response->status->value}");
        }

        return $parsedResponse;
    }

    /**
     * Create an absolute URL for provided Pyrus endpoint.
     *
     * @psalm-param scalar[] $urlParams
     *
     * @psalm-return non-empty-string
     */
    private function createEndpointUrl(PyrusEndpoint $endpoint, array $urlParams = [], ?string $forceBaseUrl = null): string
    {
        $baseUrl = $forceBaseUrl ?? ($this->token?->apiUrl ?? $this->options->defaultBaseUrl);
        $path = $endpoint->path($urlParams);

        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Parses response from Pyrus to an associative array.
     *
     * @return array<string, mixed>
     */
    private function parseResponse(PyrusResponse $response): array
    {
        /** @var array<string, mixed> */
        $res = json_decode($response->payload, true, 512, \JSON_THROW_ON_ERROR);

        return $res;
    }
}
