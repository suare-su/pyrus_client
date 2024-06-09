<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\Form;

/**
 * DTO for form entity from Pyrus.
 */
class FormList
{
    /**
     * @param string[]    $steps
     * @param FormField[] $fields
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly bool $deletedOrClosed,
        public readonly array $steps = [],
        public readonly array $fields = [],
    ) {
    }
}
