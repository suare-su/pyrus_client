<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\DataConverter;

use SuareSu\PyrusClient\Entity\Catalog;
use SuareSu\PyrusClient\Entity\CatalogHeader;
use SuareSu\PyrusClient\Entity\CatalogItem;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class EntityConverter implements NormalizerInterface
{
    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof CatalogItem
            || $data instanceof Catalog
            || $data instanceof CatalogHeader;
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
}
