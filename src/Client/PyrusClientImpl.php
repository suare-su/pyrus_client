<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Client;

use SuareSu\PyrusClient\DataConverter\PyrusDataConverter;
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

    public function __construct(
        private readonly PyrusTransport $transport,
        private readonly PyrusDataConverter $dataConverter,
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

        $response = $this->requestInternal($method, $url, $credentials);

        /** @var PyrusAuthToken */
        $token = $this->dataConverter->denormalize($response, PyrusAuthToken::class);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function request(PyrusEndpoint $endpoint, array $urlParams = [], array|object|null $payload = null): array
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
            $this->token = null;
            $response = $this->request($endpoint, $urlParams, $payload);
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
     * @param array<string, mixed>|object|null $payload
     * @param array<string, string|string[]>   $headers
     *
     * @psalm-param non-empty-string $url
     *
     * @return array<string, mixed>
     *
     * @throws PyrusTransportException
     * @throws PyrusApiException
     * @throws PyrusDataConverterException
     */
    private function requestInternal(PyrusRequestMethod $method, string $url, array|object|null $payload = null, array $headers = []): array
    {
        $normalizedPayload = null === $payload ? null : $this->dataConverter->normalize($payload);
        $headers[PyrusHeader::CONTENT_TYPE->value] = 'application/json';
        $request = new PyrusRequest($method, $url, $normalizedPayload, $headers);

        try {
            $response = $this->transport->request($request);
        } catch (\Throwable $e) {
            throw new PyrusTransportException(message: $e->getMessage(), previous: $e);
        }

        $parsedResponse = $this->dataConverter->jsonDecode($response->payload);

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
     * @psalm-param scalar[] $urlParams
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
}
