<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Pyrus;

/**
 * Enum that contains all domains for Pyrus.
 */
enum PyrusDomain: string
{
    case ACCOUNTS = 'https://accounts.pyrus.com';
    case API = 'https://api.pyrus.com';
}
