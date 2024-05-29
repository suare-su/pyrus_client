<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity;

/**
 * DTO for catalog entity from Pyrus.
 */
class Catalog
{
    /**
     * @param int[]           $supervisors
     * @param CatalogHeader[] $catalogHeaders
     * @param CatalogItem[]   $items
     */
    public function __construct(
        public readonly int $catalogId,
        public readonly string $name,
        public readonly string $sourceType,
        public readonly int $version,
        public readonly bool $deleted,
        public readonly array $supervisors = [],
        public readonly array $catalogHeaders = [],
        public readonly array $items = [],
    ) {
    }
}
