<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Gateway;

use SuareSu\PyrusClient\Client\PyrusClient;
use SuareSu\PyrusClient\Entity\Catalog\Catalog;
use SuareSu\PyrusClient\Entity\Catalog\CatalogCreate;
use SuareSu\PyrusClient\Entity\Catalog\CatalogUpdate;
use SuareSu\PyrusClient\Entity\Catalog\CatalogUpdateResponse;
use SuareSu\PyrusClient\Entity\File\File;
use SuareSu\PyrusClient\Entity\Form\Form;
use SuareSu\PyrusClient\Entity\Task\FormTask;
use SuareSu\PyrusClient\Entity\Task\FormTaskCreate;

/**
 * Object that implements all concrete requests for Pyrus.
 *
 * @psalm-api
 */
interface PyrusGateway
{
    /**
     * Return underlying client object.
     */
    public function getClient(): PyrusClient;

    /**
     * @return iterable<Catalog>
     */
    public function getCatalogs(): iterable;

    public function getCatalog(int $id, bool $includeDeleted = false): Catalog;

    public function createCatalog(CatalogCreate $catalog): Catalog;

    public function updateCatalog(int $id, CatalogUpdate $catalog): CatalogUpdateResponse;

    /**
     * @return iterable<Form>
     */
    public function getForms(): iterable;

    public function getForm(int $id): Form;

    public function createFormTask(FormTaskCreate $task): FormTask;

    public function getTask(int $id): FormTask;

    public function uploadFile(\SplFileInfo $file): File;
}
