<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\Form;

/**
 * DTO for print form entity from Pyrus.
 */
class PrintForm
{
    public function __construct(
        public readonly int $printFormId,
        public readonly string $printFormName,
    ) {
    }
}
