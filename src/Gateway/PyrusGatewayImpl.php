<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Gateway;

use SuareSu\PyrusClient\Client\PyrusClient;
use SuareSu\PyrusClient\DataConverter\PyrusDataConverter;
use SuareSu\PyrusClient\Entity\Catalog\Catalog;
use SuareSu\PyrusClient\Entity\Catalog\CatalogCreate;
use SuareSu\PyrusClient\Entity\Catalog\CatalogUpdate;
use SuareSu\PyrusClient\Entity\Catalog\CatalogUpdateResponse;
use SuareSu\PyrusClient\Entity\File\File;
use SuareSu\PyrusClient\Entity\Form\Form;
use SuareSu\PyrusClient\Entity\Task\FormTask;
use SuareSu\PyrusClient\Entity\Task\FormTaskCreate;
use SuareSu\PyrusClient\Pyrus\PyrusEndpoint;

/**
 * Basic implementation for PyrusGateway.
 */
final class PyrusGatewayImpl implements PyrusGateway
{
    public function __construct(
        private readonly PyrusClient $client,
        private readonly PyrusDataConverter $dataConverter,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getClient(): PyrusClient
    {
        return $this->client;
    }

    /**
     * {@inheritdoc}
     */
    public function getCatalogs(): iterable
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
        $raw = $this->client->request(
            PyrusEndpoint::CATALOG_READ,
            $id,
            [
                'include_deleted' => $includeDeleted,
            ]
        );

        /** @var Catalog */
        $catalog = $this->dataConverter->denormalize($raw, Catalog::class);

        return $catalog;
    }

    /**
     * {@inheritdoc}
     */
    public function createCatalog(CatalogCreate $catalog): Catalog
    {
        $raw = $this->client->request(
            endpoint: PyrusEndpoint::CATALOG_CREATE,
            payload: $this->dataConverter->normalize($catalog)
        );

        /** @var Catalog */
        $createdCatalog = $this->dataConverter->denormalize($raw, Catalog::class);

        return $createdCatalog;
    }

    /**
     * {@inheritdoc}
     */
    public function updateCatalog(int $id, CatalogUpdate $catalog): CatalogUpdateResponse
    {
        $raw = $this->client->request(
            PyrusEndpoint::CATALOG_UPDATE,
            $id,
            $this->dataConverter->normalize($catalog)
        );

        /** @var CatalogUpdateResponse */
        $updatedCatalog = $this->dataConverter->denormalize($raw, CatalogUpdateResponse::class);

        return $updatedCatalog;
    }

    /**
     * {@inheritdoc}
     */
    public function getForms(): iterable
    {
        $raw = $this->client->request(PyrusEndpoint::FORM_INDEX);
        $data = (array) ($raw['forms'] ?? []);
        $type = Form::class . '[]';

        /** @var Form[] */
        $list = $this->dataConverter->denormalize($data, $type);

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    public function getForm(int $id): Form
    {
        $raw = $this->client->request(PyrusEndpoint::FORM_READ, $id);

        /** @var Form */
        $form = $this->dataConverter->denormalize($raw, Form::class);

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function createFormTask(FormTaskCreate $task): FormTask
    {
        $raw = $this->client->request(
            endpoint: PyrusEndpoint::FORM_TASK_CREATE,
            payload: $this->dataConverter->normalize($task)
        );

        /** @var FormTask */
        $task = $this->dataConverter->denormalize($raw, FormTask::class);

        return $task;
    }

    /**
     * {@inheritdoc}
     */
    public function uploadFile(\SplFileInfo $file): File
    {
        $raw = $this->client->uploadFile(
            endpoint: PyrusEndpoint::FILE_UPLOAD,
            file: $file
        );

        /** @var File */
        $uploadedFile = $this->dataConverter->denormalize($raw, File::class);

        return $uploadedFile;
    }
}
