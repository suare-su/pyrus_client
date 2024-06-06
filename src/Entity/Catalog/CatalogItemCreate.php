<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\Catalog;

/**
 * DTO for catalog header entity from Pyrus.
 */
class CatalogItemCreate
{
    /**
     * @param string[] $values
     */
    public function __construct(
        public array $values,
    ) {
    }
}
