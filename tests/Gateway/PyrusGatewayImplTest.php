<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Tests\Gateway;

use SuareSu\PyrusClient\Client\PyrusAuthToken;
use SuareSu\PyrusClient\Client\PyrusClient;
use SuareSu\PyrusClient\Client\PyrusCredentials;
use SuareSu\PyrusClient\DataConverter\PyrusDataConverter;
use SuareSu\PyrusClient\Entity\Catalog;
use SuareSu\PyrusClient\Gateway\PyrusGatewayImpl;
use SuareSu\PyrusClient\Pyrus\PyrusEndpoint;
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

    /**
     * @test
     */
    public function testGetCatalogs(): void
    {
        $result = [
            'result' => 'value',
            'result_1' => 'value 1',
        ];
        $normalizedResult = [
            $this->mock(Catalog::class),
            $this->mock(Catalog::class),
        ];

        $client = $this->createClientAwaitsRequest(
            PyrusEndpoint::CATALOG_INDEX,
            [
                'catalogs' => $result,
            ]
        );
        $dataConverter = $this->createDataConverterAwaitsDenormalize(
            $result,
            Catalog::class . '[]',
            $normalizedResult
        );

        $gateway = new PyrusGatewayImpl($client, $dataConverter);
        $res = $gateway->getCatalogs();

        $this->assertSame($normalizedResult, $res);
    }

    /**
     * @test
     */
    public function testGetCatalog(): void
    {
        $id = 123;
        $result = [
            'result' => 'value',
            'result_1' => 'value 1',
        ];
        $normalizedResult = $this->mock(Catalog::class);

        $client = $this->createClientAwaitsRequest(
            PyrusEndpoint::CATALOG_READ,
            $result,
            [$id],
            ['include_deleted' => false]
        );
        $dataConverter = $this->createDataConverterAwaitsDenormalize(
            $result,
            Catalog::class,
            $normalizedResult
        );

        $gateway = new PyrusGatewayImpl($client, $dataConverter);
        $res = $gateway->getCatalog($id);

        $this->assertSame($normalizedResult, $res);
    }

    /**
     * Create Pyrus client mock that expects provided request data.
     */
    private function createClientAwaitsRequest(PyrusEndpoint $endpoint, array $result, array $urlParams = [], ?array $payload = null): PyrusClient
    {
        $client = $this->mock(PyrusClient::class);

        $client->expects($this->once())
            ->method('request')
            ->with(
                $this->identicalTo($endpoint),
                $this->identicalTo($urlParams),
                $this->identicalTo($payload)
            )
            ->willReturn($result);

        return $client;
    }

    /**
     * Create Pyrus data converter mock that expects provided denormalization data.
     */
    private function createDataConverterAwaitsDenormalize(array $from, string $type, mixed $to): PyrusDataConverter
    {
        $dataConverter = $this->mock(PyrusDataConverter::class);

        $dataConverter->expects($this->once())
            ->method('denormalize')
            ->with(
                $this->identicalTo($from),
                $this->identicalTo($type),
            )
            ->willReturn($to);

        return $dataConverter;
    }
}
