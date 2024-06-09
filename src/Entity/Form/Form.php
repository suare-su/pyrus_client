<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\Form;

/**
 * DTO for form entity from Pyrus.
 */
class Form
{
    /**
     * @param string[]    $steps
     * @param FormField[] $fields
     * @param PrintForm[] $printForms
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly bool $deletedOrClosed,
        public readonly array $steps = [],
        public readonly array $fields = [],
        public readonly array $printForms = [],
    ) {
    }
}
