<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Pyrus;

use SuareSu\PyrusClient\Transport\RequestMethod;

/**
 * Descriptions of all endpoints in Pyrus.
 */
enum PyrusEndpoint: string
{
    case AUTH = 'auth';

    /**
     * Returns HTTP method required for this endpoint.
     */
    public function method(): RequestMethod
    {
        return match ($this) {
            self::AUTH => RequestMethod::POST,
            default => RequestMethod::GET,
        };
    }
}
