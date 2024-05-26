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
        /** @var array<string, mixed>|null */
        public readonly ?array $payload = null,
        /** @var array<string, string|string[]> */
        public readonly array $headers = [],
    ) {
    }
}
