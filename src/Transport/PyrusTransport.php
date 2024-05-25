<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Transport;

use SuareSu\PyrusClient\Exception\PyrusTransportException;

/**
 * Facade for HTTP client. Converts data and throws errors.
 */
interface PyrusTransport
{
    /**
     * Run a single request for Pyrus API and return response object.
     *
     * @throws PyrusTransportException
     */
    public function request(Request $request): Response;
}
