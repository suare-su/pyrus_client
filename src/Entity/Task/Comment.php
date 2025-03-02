<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\Task;

use SuareSu\PyrusClient\Entity\Person\Person;

/**
 * DTO for task for comment from Pyrus.
 *
 * @psalm-api
 */
class Comment
{
    public function __construct(
        public readonly int $id,
        public readonly string $text,
        public readonly \DateTimeInterface $createDate,
        public readonly Person $author,
    ) {
    }
}
