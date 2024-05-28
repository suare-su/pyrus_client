<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Pyrus;

/**
 * List of specific Pyrus HTTP headers.
 *
 * @psalm-api
 */
enum PyrusHeader: string
{
    case AUTHORIZATION = 'Authorization';
    case CONTENT_TYPE = 'Content-type';
}
