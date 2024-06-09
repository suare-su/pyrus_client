<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Gateway;

use SuareSu\PyrusClient\Client\PyrusAuthToken;
use SuareSu\PyrusClient\Client\PyrusCredentials;
use SuareSu\PyrusClient\Entity\Catalog\Catalog;
use SuareSu\PyrusClient\Entity\Catalog\CatalogCreate;
use SuareSu\PyrusClient\Entity\Catalog\CatalogUpdate;
use SuareSu\PyrusClient\Entity\Catalog\CatalogUpdateResponse;
use SuareSu\PyrusClient\Entity\Form\FormList;

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
     * @return iterable<Catalog>
     */
    public function getCatalogs(): iterable;

    public function getCatalog(int $id, bool $includeDeleted = false): Catalog;

    public function createCatalog(CatalogCreate $catalog): Catalog;

    public function updateCatalog(int $id, CatalogUpdate $catalog): CatalogUpdateResponse;

    /**
     * @return iterable<FormList>
     */
    public function getForms(): iterable;
}
