<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Client;

use SuareSu\PyrusClient\Pyrus\PyrusEndpoint;
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
}
