<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\Catalog;

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
        public int $catalogId,
        public string $name,
        public string $sourceType,
        public int $version,
        public bool $deleted,
        public array $supervisors = [],
        public array $catalogHeaders = [],
        public array $items = [],
    ) {
    }
}
