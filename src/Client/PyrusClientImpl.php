<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Client;

use SuareSu\PyrusClient\Exception\PyrusApiException;
use SuareSu\PyrusClient\Exception\PyrusApiUnauthorizedException;
use SuareSu\PyrusClient\Exception\PyrusTransportException;
use SuareSu\PyrusClient\Pyrus\PyrusEndpoint;
use SuareSu\PyrusClient\Pyrus\PyrusHeader;
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
        private readonly PyrusClientOptions $options,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(): PyrusClientOptions
    {
        return $this->options;
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
    public function hasAuthToken(): bool
    {
        return null !== $this->token;
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
    public function hasCredentials(): bool
    {
        return null !== $this->credentials;
    }

    /**
     * {@inheritdoc}
     */
    public function auth(PyrusCredentials $credentials): PyrusAuthToken
    {
        $method = PyrusEndpoint::AUTH->method();
        $url = $this->createEndpointUrl($this->options->accountsBaseUrl, PyrusEndpoint::AUTH);
        $headers = $this->addJsonHeaders();

        $payload = [
            'login' => $credentials->login,
            'security_key' => $credentials->securityKey,
        ];
        if (null !== $credentials->personId) {
            $payload['person_id'] = $credentials->personId;
        }

        try {
            $request = new PyrusRequest($method, $url, $payload, $headers);
            $transportResponse = $this->transport->request($request, $this->options);
        } catch (\Throwable $e) {
            throw new PyrusTransportException(message: $e->getMessage(), previous: $e);
        }

        $response = $this->parseAndValidateResponse($transportResponse);

        return new PyrusAuthToken(
            (string) ($response['access_token'] ?? ''),
            (string) ($response['api_url'] ?? ''),
            (string) ($response['files_url'] ?? ''),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function request(PyrusEndpoint $endpoint, array|float|int|string $urlParams = [], ?array $payload = null): array
    {
        return $this->runWithTokenRefreshing(
            function () use ($endpoint, $urlParams, $payload): array {
                $method = $endpoint->method();
                $token = $this->getOrRequestAuthorizationToken();
                $headers = $this->addAuthHeaders($token, $this->addJsonHeaders());

                $url = $this->createEndpointUrl($token->apiUrl, $endpoint, $urlParams);
                if (PyrusRequestMethod::GET === $endpoint->method() && null !== $payload) {
                    $url = $this->applyQueryToUrl($url, $payload);
                    $payload = null;
                }

                $request = new PyrusRequest($method, $url, $payload, $headers);

                return $this->parseAndValidateResponse(
                    $this->transport->request($request, $this->options)
                );
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function uploadFile(PyrusEndpoint $endpoint, \SplFileInfo $file, array|float|int|string $urlParams = []): array
    {
        return $this->runWithTokenRefreshing(
            function () use ($endpoint, $file, $urlParams): array {
                $token = $this->getOrRequestAuthorizationToken();
                $method = $endpoint->method();
                $url = $this->createEndpointUrl($token->apiUrl, $endpoint, $urlParams);
                $headers = $this->addAuthHeaders($token);

                $request = new PyrusRequest($method, $url, null, $headers);

                return $this->parseAndValidateResponse(
                    $this->transport->uploadFile($request, $file, $this->options)
                );
            }
        );
    }

    /**
     * Create an absolute URL for provided Pyrus endpoint.
     *
     * @param array<float|int|string>|float|int|string $urlParams
     *
     * @psalm-return non-empty-string
     */
    private function createEndpointUrl(string $baseUrl, PyrusEndpoint $endpoint, array|float|int|string $urlParams = []): string
    {
        $path = $endpoint->path(
            \is_array($urlParams) ? $urlParams : [$urlParams]
        );

        return rtrim($baseUrl, '/') . '/' . trim($path, '/?');
    }

    /**
     * Add payload to url for get requests.
     *
     * @param array<string, mixed> $query
     *
     * @psalm-param non-empty-string $url
     *
     * @psalm-return non-empty-string
     *
     * @psalm-suppress MixedAssignment
     */
    private function applyQueryToUrl(string $url, array $query): string
    {
        $queryString = '';

        $queryTrueParams = [];
        $queryStringParams = [];
        foreach ($query as $key => $value) {
            if (true === $value) {
                $queryTrueParams[] = $key;
            } elseif (!\is_bool($value) && null !== $value) {
                $queryStringParams[$key] = $value;
            }
        }

        if (!empty($queryTrueParams)) {
            $queryString = implode('&', $queryTrueParams);
        }

        if (!empty($queryStringParams)) {
            $queryString .= '&' . http_build_query($queryStringParams);
        }

        return '' === $queryString ? $url : $url . '?' . ltrim($queryString, '&');
    }

    /**
     * Parse and validate response.
     *
     * @return array<string, mixed>
     */
    private function parseAndValidateResponse(PyrusResponse $response): array
    {
        $parsedResponse = $this->parseResponse($response->payload);

        $this->validateResponse($response, $parsedResponse);

        return $parsedResponse;
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

    /**
     * Check the response and thhrow exception if something is wrong.
     */
    private function validateResponse(PyrusResponse $response, array $parsedResponse): void
    {
        if (!empty($parsedResponse['error'])) {
            throw new PyrusApiException(
                (string) $parsedResponse['error'],
                (int) ($parsedResponse['error_code'] ?? 0)
            );
        }

        if (PyrusResponseStatus::UNAUTHORIZED === $response->status) {
            throw new PyrusApiUnauthorizedException('Api request is unauthorized');
        }

        if (PyrusResponseStatus::OK !== $response->status) {
            throw new PyrusTransportException("Bad response status: {$response->status->value}");
        }
    }

    /**
     * Create array of json content headers.
     *
     * @param array<string, string> $headers
     *
     * @return array<string, string>
     */
    private function addJsonHeaders(array $headers = []): array
    {
        $headers[PyrusHeader::CONTENT_TYPE->value] = 'application/json';

        return $headers;
    }

    /**
     * Create array of authorization headers.
     *
     * @param array<string, string> $headers
     *
     * @return array<string, string>
     */
    private function addAuthHeaders(PyrusAuthToken $token, array $headers = []): array
    {
        $headers[PyrusHeader::AUTHORIZATION->value] = "Bearer {$token->accessToken}";

        return $headers;
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
     * Check the excpetion on access token exception.
     *
     * @template T
     *
     * @psalm-param callable(): T $action
     *
     * @psalm-return T
     */
    private function runWithTokenRefreshing(callable $action): mixed
    {
        try {
            $result = $action();
        } catch (PyrusApiUnauthorizedException $e) {
            $this->token = null;
            $result = $action();
        } catch (PyrusApiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new PyrusTransportException(message: $e->getMessage(), previous: $e);
        }

        return $result;
    }
}
