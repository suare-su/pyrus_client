<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Pyrus;

/**
 * List of specific Pyrus HTTP headers.
 */
enum PyrusHeader: string
{
    case AUTHORIZATION = 'Authorization';
}
