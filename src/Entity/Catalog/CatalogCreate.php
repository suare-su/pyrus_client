<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\Catalog;

/**
 * DTO for catalog creation.
 *
 * @psalm-api
 */
class CatalogCreate
{
    /**
     * @param string[]            $catalogHeaders
     * @param CatalogItemCreate[] $items
     */
    public function __construct(
        public readonly string $name,
        public readonly array $catalogHeaders = [],
        public readonly array $items = [],
    ) {
    }
}
