<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Client;

/**
 * Object that stores authorization token from Pyrus.
 */
final class PyrusAuthToken
{
    public function __construct(
        public readonly string $accessToken,
        public readonly string $apiUrl,
        public readonly string $filesUrl,
    ) {
    }
}
