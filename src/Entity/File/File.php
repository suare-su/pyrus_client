<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\File;

/**
 * DTO for file from Pyrus.
 *
 * @psalm-api
 */
class File
{
    public function __construct(
        public readonly string $guid,
        public readonly string $md5Hash,
    ) {
    }
}
