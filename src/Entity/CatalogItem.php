<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity;

/**
 * DTO for catalog header entity from Pyrus.
 */
class CatalogItem
{
    /**
     * @param string[] $values
     */
    public function __construct(
        public readonly int $itemId,
        public readonly array $values,
    ) {
    }
}
