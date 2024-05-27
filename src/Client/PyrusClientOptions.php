<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Client;

use SuareSu\PyrusClient\Pyrus\PyrusBaseUrl;

/**
 * Object that stores and provides configuration for Pyrus client.
 */
final class PyrusClientOptions
{
    /** @psalm-var non-empty-string */
    public readonly string $accountsBaseUrl;

    /**
     * @psalm-suppress PropertyTypeCoercion
     */
    public function __construct(
        /* @psalm-var non-empty-string|PyrusBaseUrl */
        string|PyrusBaseUrl $accountsBaseUrl = PyrusBaseUrl::ACCOUNTS
    ) {
        $this->accountsBaseUrl = $accountsBaseUrl instanceof PyrusBaseUrl ? $accountsBaseUrl->value : $accountsBaseUrl;
    }
}
