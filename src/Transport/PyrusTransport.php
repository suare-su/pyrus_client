<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Transport;

/**
 * Facade for HTTP client. Converts data and throws errors.
 */
interface PyrusTransport
{
    /**
     * Run a single request for Pyrus API and return response object.
     */
    public function request(PyrusRequest $request): PyrusResponse;
}
