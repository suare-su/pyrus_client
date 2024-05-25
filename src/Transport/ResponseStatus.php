<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Transport;

/**
 * List of HTTP reponses.
 */
enum ResponseStatus: int
{
    case OK = 200;
    case SERVER_ERROR = 500;
}
