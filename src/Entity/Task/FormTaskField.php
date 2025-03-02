<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\Task;

/**
 * DTO for single field for task for form entity from Pyrus.
 *
 * @psalm-api
 */
class FormTaskField
{
    public function __construct(
        public readonly int $id,
        public readonly string $type,
        public readonly string $name,
        public readonly string $code,
        public readonly mixed $value,
    ) {
    }
}
