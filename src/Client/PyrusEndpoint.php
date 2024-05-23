<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Client;

use SuareSu\PyrusClient\Transport\TransportMethod;

/**
 * Descriptions of all endpoints in Pyrus.
 */
enum PyrusEndpoint: string
{
    case AUTH = 'auth';

    /**
     * Returns HTTP method required for this endpoint.
     */
    public function method(): TransportMethod
    {
        return match ($this) {
            self::AUTH => TransportMethod::POST,
            default => TransportMethod::GET,
        };
    }
}
