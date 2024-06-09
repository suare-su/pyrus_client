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
        public bool $apply = true,
        public array $catalogHeaders = [],
        public array $items = [],
    ) {
    }
}
