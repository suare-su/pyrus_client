<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\Task;

/**
 * DTO for single field for task for form entity creation from Pyrus.
 *
 * @psalm-api
 */
class FormTaskCreateField
{
    public function __construct(
        public readonly int $id,
        public readonly mixed $value,
    ) {
    }
}
