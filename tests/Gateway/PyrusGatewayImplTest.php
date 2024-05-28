<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Tests\Gateway;

use SuareSu\PyrusClient\Client\PyrusAuthToken;
use SuareSu\PyrusClient\Client\PyrusClient;
use SuareSu\PyrusClient\Client\PyrusCredentials;
use SuareSu\PyrusClient\DataConverter\PyrusDataConverter;
use SuareSu\PyrusClient\Gateway\PyrusGatewayImpl;
use SuareSu\PyrusClient\Tests\BaseCase;

/**
 * @internal
 */
final class PyrusGatewayImplTest extends BaseCase
{
    /**
     * @test
     */
    public function testUseAuthToken(): void
    {
        $token = new PyrusAuthToken('qwe', 'qwe', 'qwe');

        $client = $this->mock(PyrusClient::class);
        $client->expects($this->once())
            ->method('useAuthToken')
            ->with(
                $this->identicalTo($token)
            );

        $dataConverter = $this->mock(PyrusDataConverter::class);

        $gateway = new PyrusGatewayImpl($client, $dataConverter);
        $gateway->useAuthToken($token);
    }

    /**
     * @test
     */
    public function testUseAuthCredentials(): void
    {
        $credentials = new PyrusCredentials('qwe', 'qwe', 'qwe');

        $client = $this->mock(PyrusClient::class);
        $client->expects($this->once())
            ->method('useAuthCredentials')
            ->with(
                $this->identicalTo($credentials)
            );

        $dataConverter = $this->mock(PyrusDataConverter::class);

        $gateway = new PyrusGatewayImpl($client, $dataConverter);
        $gateway->useAuthCredentials($credentials);
    }
}
