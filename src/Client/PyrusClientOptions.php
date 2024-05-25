<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Client;

use SuareSu\PyrusClient\Pyrus\PyrusBaseUrl;

/**
 * Object that stores and provides configuration for Pyrus client.
 */
final class PyrusClientOptions
{
    public function __construct(
        /** @psalm-var non-empty-string */
        public readonly string $defaultBaseUrl = PyrusBaseUrl::API->value,
        /** @psalm-var non-empty-string */
        public readonly string $accountsBaseUrl = PyrusBaseUrl::ACCOUNTS->value
    ) {
    }
}
