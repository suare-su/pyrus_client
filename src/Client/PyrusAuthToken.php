<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Client;

/**
 * Object that stores authorization token from Pyrus.
 */
final class PyrusAuthToken
{
    public function __construct(
        /** @psalm-var non-empty-string */
        public readonly string $accessToken,
        /** @psalm-var non-empty-string */
        public readonly string $apiUrl,
        /** @psalm-var non-empty-string */
        public readonly string $filesUrl,
    ) {
    }
}
