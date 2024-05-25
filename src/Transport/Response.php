<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Transport;

/**
 * Object that contains data of a single response of Pyrus transport.
 */
final class Response
{
    public function __construct(
        public readonly ResponseStatus $status,
        public readonly string $payload,
    ) {
    }
}
