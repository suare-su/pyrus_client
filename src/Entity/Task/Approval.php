<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\Task;

use SuareSu\PyrusClient\Entity\Person\Person;

/**
 * DTO for task for approval from Pyrus.
 *
 * @psalm-api
 */
class Approval
{
    public function __construct(
        public readonly Person $person,
        public readonly string $approvalChoice,
    ) {
    }
}
