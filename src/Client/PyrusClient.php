<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Client;

use SuareSu\PyrusClient\Exception\PyrusApiException;
use SuareSu\PyrusClient\Exception\PyrusDataConverterException;
use SuareSu\PyrusClient\Exception\PyrusTransportException;

/**
 * Client that stores all Pyrus API calls and logic.
 *
 * @psalm-api
 */
interface PyrusClient
{
    /**
     * All further client requests will be authorized using the provided token.
     */
    public function useAuthToken(PyrusAuthToken $token): void;

    /**
     * Client will clear all authorisation info and try to get a new token for the provided credentials.
     */
    public function useAuthCredentials(PyrusCredentials $credentials): void;

    /**
     * Run authorization request and return auth token.
     *
     * @throws PyrusTransportException
     * @throws PyrusApiException
     * @throws PyrusDataConverterException
     */
    public function auth(PyrusCredentials $credentials): PyrusAuthToken;
}
