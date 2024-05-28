<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity;

/**
 * DTO for catalog header entity from Pyrus.
 */
class CatalogHeader
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
    ) {
    }
}
