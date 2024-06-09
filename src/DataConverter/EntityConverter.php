<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\DataConverter;

use SuareSu\PyrusClient\Entity\Catalog\Catalog;
use SuareSu\PyrusClient\Entity\Catalog\CatalogCreate;
use SuareSu\PyrusClient\Entity\Catalog\CatalogHeader;
use SuareSu\PyrusClient\Entity\Catalog\CatalogItem;
use SuareSu\PyrusClient\Entity\Catalog\CatalogItemCreate;
use SuareSu\PyrusClient\Entity\Catalog\CatalogUpdate;
use SuareSu\PyrusClient\Entity\Catalog\CatalogUpdateResponse;
use SuareSu\PyrusClient\Entity\Form\Form;
use SuareSu\PyrusClient\Entity\Form\FormField;
use SuareSu\PyrusClient\Entity\Form\PrintForm;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class EntityConverter implements DenormalizerInterface, NormalizerInterface
{
    /**
     * {@inheritDoc}
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof PrintForm
            || $data instanceof Form
            || $data instanceof FormField
            || $data instanceof CatalogUpdate
            || $data instanceof CatalogItem
            || $data instanceof CatalogCreate
            || $data instanceof Catalog
            || $data instanceof CatalogHeader
            || $data instanceof CatalogItemCreate
            || $data instanceof CatalogUpdateResponse;
    }

    /**
     * {@inheritDoc}
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        if ($object instanceof PrintForm) {
            return $this->normalizePrintForm($object);
        } elseif ($object instanceof Form) {
            return $this->normalizeForm($object);
        } elseif ($object instanceof FormField) {
            return $this->normalizeFormField($object);
        } elseif ($object instanceof CatalogUpdate) {
            return $this->normalizeCatalogUpdate($object);
        } elseif ($object instanceof CatalogItem) {
            return $this->normalizeCatalogItem($object);
        } elseif ($object instanceof CatalogCreate) {
            return $this->normalizeCatalogCreate($object);
        } elseif ($object instanceof Catalog) {
            return $this->normalizeCatalog($object);
        } elseif ($object instanceof CatalogHeader) {
            return $this->normalizeCatalogHeader($object);
        } elseif ($object instanceof CatalogItemCreate) {
            return $this->normalizeCatalogItemCreate($object);
        } elseif ($object instanceof CatalogUpdateResponse) {
            return $this->normalizeCatalogUpdateResponse($object);
        }

        throw new InvalidArgumentException("Can't normalize provided data");
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): bool {
        return PrintForm::class === $type
            || Form::class === $type
            || FormField::class === $type
            || CatalogUpdate::class === $type
            || CatalogItem::class === $type
            || CatalogCreate::class === $type
            || Catalog::class === $type
            || CatalogHeader::class === $type
            || CatalogItemCreate::class === $type
            || CatalogUpdateResponse::class === $type;
    }

    /**
     * {@inheritDoc}
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (!\is_array($data)) {
            throw new InvalidArgumentException("Can't denormalize provided data");
        }

        if (PrintForm::class === $type) {
            return $this->denormalizePrintForm($data);
        } elseif (Form::class === $type) {
            return $this->denormalizeForm($data);
        } elseif (FormField::class === $type) {
            return $this->denormalizeFormField($data);
        } elseif (CatalogUpdate::class === $type) {
            return $this->denormalizeCatalogUpdate($data);
        } elseif (CatalogItem::class === $type) {
            return $this->denormalizeCatalogItem($data);
        } elseif (CatalogCreate::class === $type) {
            return $this->denormalizeCatalogCreate($data);
        } elseif (Catalog::class === $type) {
            return $this->denormalizeCatalog($data);
        } elseif (CatalogHeader::class === $type) {
            return $this->denormalizeCatalogHeader($data);
        } elseif (CatalogItemCreate::class === $type) {
            return $this->denormalizeCatalogItemCreate($data);
        } elseif (CatalogUpdateResponse::class === $type) {
            return $this->denormalizeCatalogUpdateResponse($data);
        }

        throw new InvalidArgumentException("Can't denormalize provided type");
    }

    /**
     * {@inheritDoc}
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            PrintForm::class => true,
            Form::class => true,
            FormField::class => true,
            CatalogUpdate::class => true,
            CatalogItem::class => true,
            CatalogCreate::class => true,
            Catalog::class => true,
            CatalogHeader::class => true,
            CatalogItemCreate::class => true,
            CatalogUpdateResponse::class => true,
        ];
    }

    private function normalizePrintForm(PrintForm $object): array
    {
        return [
            'print_form_id' => $object->printFormId,
            'print_form_name' => $object->printFormName,
        ];
    }

    private function normalizeForm(Form $object): array
    {
        return [
            'id' => $object->id,
            'name' => $object->name,
            'deleted_or_closed' => $object->deletedOrClosed,
            'steps' => $object->steps,
            'fields' => array_map(fn (FormField $val): array => $this->normalizeFormField($val), $object->fields),
            'print_forms' => array_map(fn (PrintForm $val): array => $this->normalizePrintForm($val), $object->printForms),
        ];
    }

    private function normalizeFormField(FormField $object): array
    {
        return [
            'id' => $object->id,
            'type' => $object->type,
            'name' => $object->name,
            'tooltip' => $object->tooltip,
            'info' => $object->info,
        ];
    }

    private function normalizeCatalogUpdate(CatalogUpdate $object): array
    {
        return [
            'apply' => $object->apply,
            'catalog_headers' => $object->catalogHeaders,
            'items' => array_map(fn (CatalogItemCreate $val): array => $this->normalizeCatalogItemCreate($val), $object->items),
        ];
    }

    private function normalizeCatalogItem(CatalogItem $object): array
    {
        return [
            'item_id' => $object->itemId,
            'values' => $object->values,
        ];
    }

    private function normalizeCatalogCreate(CatalogCreate $object): array
    {
        return [
            'name' => $object->name,
            'catalog_headers' => $object->catalogHeaders,
            'items' => array_map(fn (CatalogItemCreate $val): array => $this->normalizeCatalogItemCreate($val), $object->items),
        ];
    }

    private function normalizeCatalog(Catalog $object): array
    {
        return [
            'catalog_id' => $object->catalogId,
            'name' => $object->name,
            'source_type' => $object->sourceType,
            'version' => $object->version,
            'deleted' => $object->deleted,
            'supervisors' => $object->supervisors,
            'catalog_headers' => array_map(fn (CatalogHeader $val): array => $this->normalizeCatalogHeader($val), $object->catalogHeaders),
            'items' => array_map(fn (CatalogItem $val): array => $this->normalizeCatalogItem($val), $object->items),
        ];
    }

    private function normalizeCatalogHeader(CatalogHeader $object): array
    {
        return [
            'name' => $object->name,
            'type' => $object->type,
        ];
    }

    private function normalizeCatalogItemCreate(CatalogItemCreate $object): array
    {
        return [
            'values' => $object->values,
        ];
    }

    private function normalizeCatalogUpdateResponse(CatalogUpdateResponse $object): array
    {
        return [
            'apply' => $object->apply,
            'added' => array_map(fn (CatalogItemCreate $val): array => $this->normalizeCatalogItemCreate($val), $object->added),
            'deleted' => array_map(fn (CatalogItemCreate $val): array => $this->normalizeCatalogItemCreate($val), $object->deleted),
            'catalog_headers' => array_map(fn (CatalogHeader $val): array => $this->normalizeCatalogHeader($val), $object->catalogHeaders),
        ];
    }

    /**
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    private function denormalizePrintForm(array $data): PrintForm
    {
        return new PrintForm(
            (int) ($data['print_form_id'] ?? 0),
            (string) ($data['print_form_name'] ?? ''),
        );
    }

    /**
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    private function denormalizeForm(array $data): Form
    {
        return new Form(
            (int) ($data['id'] ?? 0),
            (string) ($data['name'] ?? ''),
            (bool) ($data['deleted_or_closed'] ?? false),
            array_map(fn (mixed $val): string => (string) $val, (array) ($data['steps'] ?? [])),
            array_map(fn (array $val): FormField => $this->denormalizeFormField($val), (array) ($data['fields'] ?? [])),
            array_map(fn (array $val): PrintForm => $this->denormalizePrintForm($val), (array) ($data['print_forms'] ?? [])),
        );
    }

    /**
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    private function denormalizeFormField(array $data): FormField
    {
        return new FormField(
            (int) ($data['id'] ?? 0),
            (string) ($data['type'] ?? ''),
            (string) ($data['name'] ?? ''),
            (string) ($data['tooltip'] ?? ''),
            (array) ($data['info'] ?? []),
        );
    }

    /**
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    private function denormalizeCatalogUpdate(array $data): CatalogUpdate
    {
        return new CatalogUpdate(
            (bool) ($data['apply'] ?? false),
            array_map(fn (mixed $val): string => (string) $val, (array) ($data['catalog_headers'] ?? [])),
            array_map(fn (array $val): CatalogItemCreate => $this->denormalizeCatalogItemCreate($val), (array) ($data['items'] ?? [])),
        );
    }

    /**
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    private function denormalizeCatalogItem(array $data): CatalogItem
    {
        return new CatalogItem(
            (int) ($data['item_id'] ?? 0),
            array_map(fn (mixed $val): string => (string) $val, (array) ($data['values'] ?? [])),
        );
    }

    /**
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    private function denormalizeCatalogCreate(array $data): CatalogCreate
    {
        return new CatalogCreate(
            (string) ($data['name'] ?? ''),
            array_map(fn (mixed $val): string => (string) $val, (array) ($data['catalog_headers'] ?? [])),
            array_map(fn (array $val): CatalogItemCreate => $this->denormalizeCatalogItemCreate($val), (array) ($data['items'] ?? [])),
        );
    }

    /**
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    private function denormalizeCatalog(array $data): Catalog
    {
        return new Catalog(
            (int) ($data['catalog_id'] ?? 0),
            (string) ($data['name'] ?? ''),
            (string) ($data['source_type'] ?? ''),
            (int) ($data['version'] ?? 0),
            (bool) ($data['deleted'] ?? false),
            array_map(fn (mixed $val): int => (int) $val, (array) ($data['supervisors'] ?? [])),
            array_map(fn (array $val): CatalogHeader => $this->denormalizeCatalogHeader($val), (array) ($data['catalog_headers'] ?? [])),
            array_map(fn (array $val): CatalogItem => $this->denormalizeCatalogItem($val), (array) ($data['items'] ?? [])),
        );
    }

    /**
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    private function denormalizeCatalogHeader(array $data): CatalogHeader
    {
        return new CatalogHeader(
            (string) ($data['name'] ?? ''),
            (string) ($data['type'] ?? ''),
        );
    }

    /**
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    private function denormalizeCatalogItemCreate(array $data): CatalogItemCreate
    {
        return new CatalogItemCreate(
            array_map(fn (mixed $val): string => (string) $val, (array) ($data['values'] ?? [])),
        );
    }

    /**
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    private function denormalizeCatalogUpdateResponse(array $data): CatalogUpdateResponse
    {
        return new CatalogUpdateResponse(
            (bool) ($data['apply'] ?? false),
            array_map(fn (array $val): CatalogItemCreate => $this->denormalizeCatalogItemCreate($val), (array) ($data['added'] ?? [])),
            array_map(fn (array $val): CatalogItemCreate => $this->denormalizeCatalogItemCreate($val), (array) ($data['deleted'] ?? [])),
            array_map(fn (array $val): CatalogHeader => $this->denormalizeCatalogHeader($val), (array) ($data['catalog_headers'] ?? [])),
        );
    }
}
