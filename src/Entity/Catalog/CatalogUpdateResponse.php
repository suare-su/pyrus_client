<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\Catalog;

/**
 * DTO for catalog update response.
 */
class CatalogUpdateResponse
{
    /**
     * @param CatalogItemCreate[] $added
     * @param CatalogItemCreate[] $deleted
     * @param CatalogHeader[]     $catalogHeaders
     */
    public function __construct(
        public readonly bool $apply = true,
        public readonly array $added = [],
        public readonly array $deleted = [],
        public readonly array $catalogHeaders = [],
    ) {
    }
}
