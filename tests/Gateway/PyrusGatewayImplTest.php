<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Tests\Gateway;

use SuareSu\PyrusClient\Client\PyrusAuthToken;
use SuareSu\PyrusClient\Client\PyrusClient;
use SuareSu\PyrusClient\Client\PyrusCredentials;
use SuareSu\PyrusClient\DataConverter\PyrusDataConverter;
use SuareSu\PyrusClient\Entity\Catalog\Catalog;
use SuareSu\PyrusClient\Entity\Catalog\CatalogCreate;
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
            $id,
            [
                'include_deleted' => false,
            ]
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
     * @test
     */
    public function testCreateCatalog(): void
    {
        $catalogCreate = $this->mock(CatalogCreate::class);
        $catalogCreateDenormalized = [
            'catalog_create_denormalized' => 'value',
            'catalog_create_denormalized_1' => 'value 1',
        ];
        $result = [
            'result' => 'value',
            'result_1' => 'value 1',
        ];
        $normalizedResult = $this->mock(Catalog::class);

        $client = $this->createClientAwaitsRequest(
            endpoint: PyrusEndpoint::CATALOG_CREATE,
            result: $result,
            payload: $catalogCreateDenormalized
        );
        $dataConverter = $this->createDataConverter(
            [
                [
                    $catalogCreate,
                    $catalogCreateDenormalized,
                ],
            ],
            [
                [
                    $result,
                    Catalog::class,
                    $normalizedResult,
                ],
            ]
        );

        $gateway = new PyrusGatewayImpl($client, $dataConverter);
        $res = $gateway->createCatalog($catalogCreate);

        $this->assertSame($normalizedResult, $res);
    }

    /**
     * Create Pyrus client mock that expects provided request data.
     */
    private function createClientAwaitsRequest(PyrusEndpoint $endpoint, array $result, array|int|float|string $urlParams = [], ?array $payload = null): PyrusClient
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
        return $this->createDataConverter(denormalize: [
            [
                $from,
                $type,
                $to,
            ],
        ]);
    }

    /**
     * Create Pyrus data converter mock.
     *
     * @psalm-suppress MixedInferredReturnType
     * @psalm-suppress MixedReturnStatement
     */
    private function createDataConverter(array $normalize = [], array $denormalize = []): PyrusDataConverter
    {
        $dataConverter = $this->mock(PyrusDataConverter::class);

        $normalizeCount = \count($normalize);
        if ($normalizeCount > 0) {
            $dataConverter->expects($this->exactly($normalizeCount))
                ->method('normalize')
                ->willReturnCallback(
                    function (array|object $data) use ($normalize): array {
                        foreach ($normalize as $item) {
                            if (isset($item[0], $item[1]) && $data === $item[0]) {
                                return $item[1];
                            }
                        }

                        return [];
                    }
                );
        } else {
            $dataConverter->expects($this->never())->method('normalize');
        }

        $denormalizeCount = \count($denormalize);
        if ($denormalizeCount > 0) {
            $dataConverter->expects($this->exactly($denormalizeCount))
                ->method('denormalize')
                ->willReturnCallback(
                    function (mixed $data, string $type) use ($denormalize): object|array {
                        foreach ($denormalize as $item) {
                            if (isset($item[0], $item[1], $item[2]) && $data === $item[0] && $item[1] === $type) {
                                return $item[2];
                            }
                        }

                        return [];
                    }
                );
        } else {
            $dataConverter->expects($this->never())->method('denormalize');
        }

        return $dataConverter;
    }
}
