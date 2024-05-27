<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Tests\Pyrus;

use SuareSu\PyrusClient\Pyrus\PyrusEndpoint;
use SuareSu\PyrusClient\Tests\BaseCase;
use SuareSu\PyrusClient\Transport\PyrusRequestMethod;

/**
 * @internal
 */
final class PyrusEndpointTest extends BaseCase
{
    /**
     * @dataProvider provideMethod
     */
    public function testMethod(PyrusEndpoint $endpoint, PyrusRequestMethod $expectedMethod): void
    {
        $this->assertSame($expectedMethod, $endpoint->method());
    }

    public static function provideMethod(): array
    {
        return [
            'get' => [
                PyrusEndpoint::CATALOG_INDEX,
                PyrusRequestMethod::GET,
            ],
            'post' => [
                PyrusEndpoint::AUTH,
                PyrusRequestMethod::POST,
            ],
            'put' => [
                PyrusEndpoint::CATALOG_CREATE,
                PyrusRequestMethod::PUT,
            ],
        ];
    }

    /**
     * @param array<float|int|string> $params
     *
     * @dataProvider providePath
     */
    public function testPath(PyrusEndpoint $endpoint, array $params, \Exception|string $expected): void
    {
        if ($expected instanceof \Exception) {
            $this->expectExceptionObject($expected);
        }

        $path = $endpoint->path($params);

        if (!($expected instanceof \Exception)) {
            $this->assertSame($expected, $path);
        }
    }

    public static function providePath(): array
    {
        return [
            'path without params' => [
                PyrusEndpoint::AUTH,
                [],
                '/auth',
            ],
            'path with params' => [
                PyrusEndpoint::CATALOG_UPDATE,
                [123],
                '/catalogs/123',
            ],
            'params count exception' => [
                PyrusEndpoint::CATALOG_UPDATE,
                [],
                new \InvalidArgumentException('Number of params'),
            ],
        ];
    }
}
