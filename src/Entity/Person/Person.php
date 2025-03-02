<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\Person;

/**
 * DTO for task for person from Pyrus.
 *
 * @psalm-api
 */
class Person
{
    public function __construct(
        public readonly int $id,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $type,
    ) {
    }
}
