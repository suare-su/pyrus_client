<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\Attachment;

/**
 * DTO for attachment from Pyrus.
 *
 * @psalm-api
 */
class Attachment
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $size,
        public readonly string $md5,
        public readonly string $url,
        public readonly int $version,
        public readonly int $rootId,
    ) {
    }
}
