<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Gateway;

use SuareSu\PyrusClient\Client\PyrusAuthToken;
use SuareSu\PyrusClient\Client\PyrusClient;
use SuareSu\PyrusClient\Client\PyrusCredentials;
use SuareSu\PyrusClient\DataConverter\PyrusDataConverter;
use SuareSu\PyrusClient\Entity\Catalog;
use SuareSu\PyrusClient\Pyrus\PyrusEndpoint;

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

    /**
     * {@inheritdoc}
     */
    public function getCatalogs(): array
    {
        $raw = $this->client->request(PyrusEndpoint::CATALOG_INDEX);
        $data = (array) ($raw['catalogs'] ?? []);
        $type = Catalog::class . '[]';

        /** @var Catalog[] */
        $list = $this->dataConverter->denormalize($data, $type);

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    public function getCatalog(int $id, bool $includeDeleted = false): Catalog
    {
        $urlParams = [$id];
        $payload = ['include_deleted' => $includeDeleted];
        $raw = $this->client->request(PyrusEndpoint::CATALOG_READ, $urlParams, $payload);

        /** @var Catalog */
        $catalog = $this->dataConverter->denormalize($raw, Catalog::class);

        return $catalog;
    }
}
