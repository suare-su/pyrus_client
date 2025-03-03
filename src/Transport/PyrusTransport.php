<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Transport;

use SuareSu\PyrusClient\Client\PyrusClientOptions;

/**
 * Facade for HTTP client.
 *
 * @psalm-api
 */
interface PyrusTransport
{
    /**
     * Run a single request for Pyrus API and return response object.
     */
    public function request(PyrusRequest $request, ?PyrusClientOptions $options = null): PyrusResponse;

    /**
     * Upload provided file to Pyrus.
     */
    public function uploadFile(PyrusRequest $request, \SplFileInfo $file, ?PyrusClientOptions $options = null): PyrusResponse;
}
