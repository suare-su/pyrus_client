<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Transport;

/**
 * List of allowed HTTP methods.
 */
enum TransportMethod: string
{
    case POST = 'POST';
    case GET = 'GET';
}
