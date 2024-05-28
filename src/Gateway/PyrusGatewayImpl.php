<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Gateway;

use SuareSu\PyrusClient\Client\PyrusAuthToken;
use SuareSu\PyrusClient\Client\PyrusClient;
use SuareSu\PyrusClient\Client\PyrusCredentials;
use SuareSu\PyrusClient\DataConverter\PyrusDataConverter;

/**
 * Basic implementation for PyrusGateway.
 */
final class PyrusGatewayImpl implements PyrusGateway
{
    public function __construct(
        private readonly PyrusClient $client,
        private readonly PyrusDataConverter $dataConverter
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function useAuthToken(PyrusAuthToken $token): void
    {
        $this->client->useAuthToken($token);
    }

    /**
     * {@inheritdoc}
     */
    public function useAuthCredentials(PyrusCredentials $credentials): void
    {
        $this->client->useAuthCredentials($credentials);
    }
}
