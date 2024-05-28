<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Gateway;

use SuareSu\PyrusClient\Client\PyrusAuthToken;
use SuareSu\PyrusClient\Client\PyrusCredentials;
use SuareSu\PyrusClient\Entity\Catalog;

/**
 * Object that implements all concrete requests for Pyrus.
 *
 * @psalm-api
 */
interface PyrusGateway
{
    /**
     * All further client requests will be authorized using the provided token.
     */
    public function useAuthToken(PyrusAuthToken $token): void;

    /**
     * Client will clear all authorisation info and try to get a new token for the provided credentials.
     */
    public function useAuthCredentials(PyrusCredentials $credentials): void;

    /**
     * @return array<int, Catalog>
     */
    public function getCatalogs(): array;

    public function getCatalog(int $id, bool $includeDeleted = false): Catalog;
}
