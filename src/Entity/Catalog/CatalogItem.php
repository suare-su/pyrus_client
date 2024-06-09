<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\Catalog;

/**
 * DTO for catalog header entity from Pyrus.
 */
class CatalogItem
{
    /**
     * @param array<string, string> $values
     */
    public function __construct(
        public readonly int $itemId,
        public readonly array $values,
    ) {
    }
}
