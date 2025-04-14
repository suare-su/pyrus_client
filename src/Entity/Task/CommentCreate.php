<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\Task;

/**
 * DTO for task to create new comment in Pyrus.
 *
 * @psalm-api
 */
class CommentCreate
{
    public function __construct(
        public readonly string $text,
    ) {
    }
}
