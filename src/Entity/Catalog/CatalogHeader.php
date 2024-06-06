<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\Catalog;

/**
 * DTO for catalog header entity from Pyrus.
 */
class CatalogHeader
{
    public function __construct(
        public string $name,
        public string $type,
    ) {
    }
}