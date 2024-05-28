<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Transport;

/**
 * List of allowed HTTP methods.
 */
enum PyrusRequestMethod: string
{
    case POST = 'POST';
    case PUT = 'PUT';
    case GET = 'GET';
}
