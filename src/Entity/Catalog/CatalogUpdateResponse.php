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
        public bool $apply = true,
        public array $added = [],
        public array $deleted = [],
        public array $catalogHeaders = [],
    ) {
    }
}
