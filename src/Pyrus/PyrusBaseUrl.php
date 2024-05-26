<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Pyrus;

/**
 * Enum that contains all domains for Pyrus.
 */
enum PyrusBaseUrl: string
{
    case ACCOUNTS = 'https://accounts.pyrus.com/api/v4';
    case API = 'https://api.pyrus.com/v4';
}
