<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Tests\Gateway;

use SuareSu\PyrusClient\Client\PyrusClient;
use SuareSu\PyrusClient\DataConverter\PyrusDataConverter;
use SuareSu\PyrusClient\Entity\Catalog\Catalog;
use SuareSu\PyrusClient\Entity\Catalog\CatalogCreate;
use SuareSu\PyrusClient\Entity\Catalog\CatalogUpdate;
use SuareSu\PyrusClient\Entity\Catalog\CatalogUpdateResponse;
use SuareSu\PyrusClient\Entity\Form\Form;
use SuareSu\PyrusClient\Entity\Task\FormTask;
use SuareSu\PyrusClient\Entity\Task\FormTaskCreate;
use SuareSu\PyrusClient\Gateway\PyrusGatewayImpl;
use SuareSu\PyrusClient\Pyrus\PyrusEndpoint;
use SuareSu\PyrusClient\Tests\BaseCase;

/**
 * @internal
 */
final class PyrusGatewayImplTest extends BaseCase
{
    private const ID = 123;
    private const RESULT = [
        'result_1' => 'result 1',
        'result_2' => 'result 2',
    ];
    private const DENORMALIZED_INPUT = [
        'denormalized_1' => 'denormalized 1',
        'denormalized_2' => 'denormalized 2',
    ];

    /**
     * @test
     */
    public function testGetClient(): void
    {
        $client = $this->mock(PyrusClient::class);
        $dataConverter = $this->mock(PyrusDataConverter::class);

        $gateway = new PyrusGatewayImpl($client, $dataConverter);
        $res = $gateway->getClient();

        $this->assertSame($client, $res);
    }

    /**
     * @test
     */
    public function testGetCatalogs(): void
    {
        $normalizedResult = [
            $this->mock(Catalog::class),
            $this->mock(Catalog::class),
        ];

        $client = $this->createClientAwaitsRequest(
            PyrusEndpoint::CATALOG_INDEX,
            [
                'catalogs' => self::RESULT,
            ]
        );
        $dataConverter = $this->createDataConverterAwaitsDenormalize(
            self::RESULT,
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
        $normalizedResult = $this->mock(Catalog::class);

        $client = $this->createClientAwaitsRequest(
            PyrusEndpoint::CATALOG_READ,
            self::RESULT,
            self::ID,
            [
                'include_deleted' => false,
            ]
        );
        $dataConverter = $this->createDataConverterAwaitsDenormalize(
            self::RESULT,
            Catalog::class,
            $normalizedResult
        );

        $gateway = new PyrusGatewayImpl($client, $dataConverter);
        $res = $gateway->getCatalog(self::ID);

        $this->assertSame($normalizedResult, $res);
    }

    /**
     * @test
     */
    public function testCreateCatalog(): void
    {
        $catalogCreate = $this->mock(CatalogCreate::class);
        $normalizedResult = $this->mock(Catalog::class);

        $client = $this->createClientAwaitsRequest(
            endpoint: PyrusEndpoint::CATALOG_CREATE,
            result: self::RESULT,
            payload: self::DENORMALIZED_INPUT
        );
        $dataConverter = $this->createDataConverter(
            [
                [
                    $catalogCreate,
                    self::DENORMALIZED_INPUT,
                ],
            ],
            [
                [
                    self::RESULT,
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
     * @test
     */
    public function testUpdateCatalog(): void
    {
        $catalogUpdate = $this->mock(CatalogUpdate::class);
        $normalizedResult = $this->mock(CatalogUpdateResponse::class);

        $client = $this->createClientAwaitsRequest(
            PyrusEndpoint::CATALOG_UPDATE,
            self::RESULT,
            self::ID,
            self::DENORMALIZED_INPUT
        );
        $dataConverter = $this->createDataConverter(
            [
                [
                    $catalogUpdate,
                    self::DENORMALIZED_INPUT,
                ],
            ],
            [
                [
                    self::RESULT,
                    CatalogUpdateResponse::class,
                    $normalizedResult,
                ],
            ]
        );

        $gateway = new PyrusGatewayImpl($client, $dataConverter);
        $res = $gateway->updateCatalog(self::ID, $catalogUpdate);

        $this->assertSame($normalizedResult, $res);
    }

    /**
     * @test
     */
    public function testGetForms(): void
    {
        $normalizedResult = [
            $this->mock(Form::class),
            $this->mock(Form::class),
        ];

        $client = $this->createClientAwaitsRequest(
            PyrusEndpoint::FORM_INDEX,
            [
                'forms' => self::RESULT,
            ]
        );
        $dataConverter = $this->createDataConverterAwaitsDenormalize(
            self::RESULT,
            Form::class . '[]',
            $normalizedResult
        );

        $gateway = new PyrusGatewayImpl($client, $dataConverter);
        $res = $gateway->getForms();

        $this->assertSame($normalizedResult, $res);
    }

    /**
     * @test
     */
    public function testGetForm(): void
    {
        $normalizedResult = $this->mock(Form::class);

        $client = $this->createClientAwaitsRequest(
            PyrusEndpoint::FORM_READ,
            self::RESULT,
            self::ID
        );
        $dataConverter = $this->createDataConverterAwaitsDenormalize(
            self::RESULT,
            Form::class,
            $normalizedResult
        );

        $gateway = new PyrusGatewayImpl($client, $dataConverter);
        $res = $gateway->getForm(self::ID);

        $this->assertSame($normalizedResult, $res);
    }

    /**
     * @test
     */
    public function testCreateFormTask(): void
    {
        $formTaskCreate = $this->mock(FormTaskCreate::class);
        $normalizedResult = $this->mock(FormTask::class);

        $client = $this->createClientAwaitsRequest(
            PyrusEndpoint::FORM_TASK_CREATE,
            self::RESULT,
            [],
            self::DENORMALIZED_INPUT
        );
        $dataConverter = $this->createDataConverter(
            [
                [
                    $formTaskCreate,
                    self::DENORMALIZED_INPUT,
                ],
            ],
            [
                [
                    self::RESULT,
                    FormTask::class,
                    $normalizedResult,
                ],
            ]
        );

        $gateway = new PyrusGatewayImpl($client, $dataConverter);
        $res = $gateway->createFormTask($formTaskCreate);

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
