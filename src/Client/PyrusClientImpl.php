<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Client;

use SuareSu\PyrusClient\Transport\PyrusTransportManager;

/**
 * Basic implementation for PyrusClient interface.
 *
 * @psalm-api
 */
final class PyrusClientImpl implements PyrusClient
{
    private ?AuthToken $token = null;

    private ?Credentials $credentials = null;

    public function __construct(
        private readonly PyrusTransportManager $transportManager,
        private readonly Options $options
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function useAuthToken(AuthToken $token): void
    {
        $this->token = $token;
    }

    /**
     * {@inheritdoc}
     */
    public function useAuthCredentials(Credentials $credentials): void
    {
        $this->token = null;
        $this->credentials = $credentials;
    }
}
