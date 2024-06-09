<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Tests\Client;

use SuareSu\PyrusClient\Client\PyrusClientOptions;
use SuareSu\PyrusClient\Pyrus\PyrusBaseUrl;
use SuareSu\PyrusClient\Tests\BaseCase;

/**
 * @internal
 */
final class PyrusClientOptionsTest extends BaseCase
{
    /**
     * @test
     */
    public function testConstructStringUrl(): void
    {
        $url = 'http://test.test';
        $options = new PyrusClientOptions(accountsBaseUrl: $url);

        $this->assertSame($url, $options->accountsBaseUrl);
    }

    /**
     * @test
     */
    public function testConstructEnumUrl(): void
    {
        $url = PyrusBaseUrl::API;
        $options = new PyrusClientOptions(accountsBaseUrl: $url);

        $this->assertSame($url->value, $options->accountsBaseUrl);
    }
}
