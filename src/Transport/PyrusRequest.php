<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Transport;

/**
 * Object that contains data of a single request for Pyrus transport.
 */
final class PyrusRequest
{
    public function __construct(
        public readonly PyrusRequestMethod $method,
        /** @psalm-var non-empty-string */
        public readonly string $url,
        /** @var array<string, mixed> */
        public readonly array $payload = [],
        /** @var array<string, string|string[]> */
        public readonly array $headers = [],
    ) {
    }
}
