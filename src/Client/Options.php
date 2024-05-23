<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Client;

/**
 * Object that stores and provides configuration for Pyrus client.
 */
final class Options
{
    public function __construct(
        /** @psalm-var non-empty-string */
        public readonly string $defaultDomain = PyrusDomain::API->value,
        /** @psalm-var non-empty-string */
        public readonly string $accountsDomain = PyrusDomain::ACCOUNTS->value
    ) {
    }
}
