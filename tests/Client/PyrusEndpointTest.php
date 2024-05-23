<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Tests\Client;

use SuareSu\PyrusClient\Client\PyrusEndpoint;
use SuareSu\PyrusClient\Tests\BaseCase;
use SuareSu\PyrusClient\Transport\TransportMethod;

/**
 * Base test case for all tests.
 *
 * @internal
 */
final class PyrusEndpointTest extends BaseCase
{
    public function testMethod(): void
    {
        $method = PyrusEndpoint::AUTH->method();

        $this->assertSame(TransportMethod::POST, $method);
    }
}
