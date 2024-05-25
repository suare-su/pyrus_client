<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Client;

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
}
