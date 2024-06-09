<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Transport;

/**
 * List of HTTP reponses.
 */
enum PyrusResponseStatus: int
{
    case OK = 200;
    case SERVER_ERROR = 500;
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case NOT_FOUND = 404;
}
