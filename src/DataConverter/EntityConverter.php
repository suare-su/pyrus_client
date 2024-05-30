<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\DataConverter;

use SuareSu\PyrusClient\Entity\Catalog;
use SuareSu\PyrusClient\Entity\CatalogHeader;
use SuareSu\PyrusClient\Entity\CatalogItem;
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
        return $data instanceof CatalogItem
            || $data instanceof Catalog
            || $data instanceof CatalogHeader;
    }

    /**
     * {@inheritDoc}
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        if ($object instanceof CatalogItem) {
            return $this->normalizeCatalogItem($object);
        } elseif ($object instanceof Catalog) {
            return $this->normalizeCatalog($object);
        } elseif ($object instanceof CatalogHeader) {
            return $this->normalizeCatalogHeader($object);
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
        return CatalogItem::class === $type
            || Catalog::class === $type
            || CatalogHeader::class === $type;
    }

    /**
     * {@inheritDoc}
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (!\is_array($data)) {
            throw new InvalidArgumentException("Can't denormalize provided data");
        }

        if (CatalogItem::class === $type) {
            return $this->denormalizeCatalogItem($data);
        } elseif (Catalog::class === $type) {
            return $this->denormalizeCatalog($data);
        } elseif (CatalogHeader::class === $type) {
            return $this->denormalizeCatalogHeader($data);
        }

        throw new InvalidArgumentException("Can't denormalize provided type");
    }

    /**
     * {@inheritDoc}
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            CatalogItem::class => true,
            Catalog::class => true,
            CatalogHeader::class => true,
        ];
    }

    private function normalizeCatalogItem(CatalogItem $object): array
    {
        return [
            'item_id' => $object->itemId,
            'values' => $object->values,
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

    private function denormalizeCatalogItem(array $data): CatalogItem
    {
        return new CatalogItem(
            (int) ($data['item_id'] ?? 0),
            array_map(fn (mixed $val): string => (string) $val, (array) ($data['values'] ?? [])),
        );
    }

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

    private function denormalizeCatalogHeader(array $data): CatalogHeader
    {
        return new CatalogHeader(
            (string) ($data['name'] ?? ''),
            (string) ($data['type'] ?? ''),
        );
    }
}
