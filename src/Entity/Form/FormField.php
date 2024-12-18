<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\Form;

/**
 * DTO for form field entity from Pyrus.
 *
 * @psalm-api
 */
class FormField
{
    /**
     * @param array<string, mixed> $info
     */
    public function __construct(
        public readonly int $id,
        public readonly FormFieldType $type,
        public readonly string $name,
        public readonly string $tooltip,
        public readonly array $info = [],
    ) {
    }
}
