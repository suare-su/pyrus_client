<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Client;

/**
 * Object that stores credentials for Pyrus authorization.
 */
final class Credentials
{
    public function __construct(
        /** @psalm-var non-empty-string */
        public readonly string $login,
        /** @psalm-var non-empty-string */
        public readonly string $securityKey,
        /** @psalm-var non-empty-string|null */
        public readonly ?string $personId = null,
    ) {
    }
}
