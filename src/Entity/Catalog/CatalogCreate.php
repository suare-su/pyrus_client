<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\Catalog;

/**
 * DTO for catalog creation.
 */
class CatalogCreate
{
    /**
     * @param string[]            $catalogHeaders
     * @param CatalogItemCreate[] $items
     */
    public function __construct(
        public string $name,
        public array $catalogHeaders = [],
        public array $items = [],
    ) {
    }
}
