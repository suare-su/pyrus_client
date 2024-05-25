<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Tests\Client;

use SuareSu\PyrusClient\Pyrus\PyrusEndpoint;
use SuareSu\PyrusClient\Tests\BaseCase;
use SuareSu\PyrusClient\Transport\RequestMethod;

/**
 * Base test case for all tests.
 *
 * @internal
 */
final class PyrusEndpointTest extends BaseCase
{
    /**
     * @dataProvider provideMethod
     */
    public function testMethod(PyrusEndpoint $endpoint, RequestMethod $expectedMethod): void
    {
        $this->assertSame($expectedMethod, $endpoint->method());
    }

    public static function provideMethod(): array
    {
        return [
            'post' => [
                PyrusEndpoint::AUTH,
                RequestMethod::POST,
            ],
        ];
    }
}
