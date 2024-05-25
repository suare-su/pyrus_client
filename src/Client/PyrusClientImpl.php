<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Client;

use SuareSu\PyrusClient\Exception\PyrusTransportException;
use SuareSu\PyrusClient\Pyrus\PyrusEndpoint;
use SuareSu\PyrusClient\Transport\PyrusTransport;
use SuareSu\PyrusClient\Transport\Request;
use SuareSu\PyrusClient\Transport\RequestMethod;
use SuareSu\PyrusClient\Transport\Response;

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
     * Create an absolute URL for provided Pyrus endpoint.
     *
     * @psalm-param scalar[] $params
     */
    private function createEndpointUrl(PyrusEndpoint $endpoint, array $params = [], ?string $forceBaseUrl = null): string
    {
        $baseUrl = $forceBaseUrl ?? ($this->token?->apiUrl ?? $this->options->defaultBaseUrl);
        $path = $endpoint->path($params);

        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Request data from Pyrus API using provided data.
     *
     * @psalm-param non-empty-string $url
     * @psalm-param array<string, mixed> $payload
     * @psalm-param array<string, string|string[]> $headers
     */
    private function requestPyrus(RequestMethod $method, string $url, array $payload, array $headers = []): Response
    {
        $request = new Request($url, $payload, $headers, $method);

        try {
            $response = $this->transport->request($request);
        } catch (PyrusTransportException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new PyrusTransportException($e->getMessage(), 0, $e);
        }

        return $response;
    }
}
