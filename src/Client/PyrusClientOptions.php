<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Client;

use SuareSu\PyrusClient\Pyrus\PyrusBaseUrl;

/**
 * Object that stores and provides configuration for Pyrus client.
 */
final class PyrusClientOptions
{
    public const DEFAULT_TIMEOUT = 15;
    public const DEFAULT_RETRIES = 3;
    public const DEFAULT_RETRY_TIMEOUT = 5;

    /** @psalm-var non-empty-string */
    public readonly string $accountsBaseUrl;

    /**
     * @psalm-suppress PropertyTypeCoercion
     */
    public function __construct(
        /* @psalm-var non-empty-string|PyrusBaseUrl */
        string|PyrusBaseUrl $accountsBaseUrl = PyrusBaseUrl::ACCOUNTS,
        public readonly int $timeout = self::DEFAULT_TIMEOUT,
        public readonly int $retries = self::DEFAULT_RETRIES,
        public readonly int $retryTimeout = self::DEFAULT_RETRY_TIMEOUT,
    ) {
        $this->accountsBaseUrl = $accountsBaseUrl instanceof PyrusBaseUrl ? $accountsBaseUrl->value : $accountsBaseUrl;
    }
}
