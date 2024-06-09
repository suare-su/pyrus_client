<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\Catalog;

/**
 * DTO for catalog update.
 */
class CatalogUpdate
{
    /**
     * @param string[]            $catalogHeaders
     * @param CatalogItemCreate[] $items
     */
    public function __construct(
        public readonly bool $apply = true,
        public readonly array $catalogHeaders = [],
        public readonly array $items = [],
    ) {
    }
}
