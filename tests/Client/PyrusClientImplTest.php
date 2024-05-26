<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Tests\Client;

use SuareSu\PyrusClient\Client\PyrusAuthToken;
use SuareSu\PyrusClient\Client\PyrusClientImpl;
use SuareSu\PyrusClient\Client\PyrusClientOptions;
use SuareSu\PyrusClient\Client\PyrusCredentials;
use SuareSu\PyrusClient\DataConverter\PyrusDataConverter;
use SuareSu\PyrusClient\Pyrus\PyrusBaseUrl;
use SuareSu\PyrusClient\Pyrus\PyrusEndpoint;
use SuareSu\PyrusClient\Tests\BaseCase;
use SuareSu\PyrusClient\Transport\PyrusRequest;
use SuareSu\PyrusClient\Transport\PyrusResponse;
use SuareSu\PyrusClient\Transport\PyrusResponseStatus;
use SuareSu\PyrusClient\Transport\PyrusTransport;

/**
 * @internal
 */
final class PyrusClientImplTest extends BaseCase
{
    /**
     * @test
     */
    public function testAuth(): void
    {
        $credentials = new PyrusCredentials('test', 'test', 'test');
        $normalizedCredentials = [
            'test' => 'test',
        ];

        $authTokenJson = 'auth_token_json';
        $authTokenArray = [
            'test' => 'auth_token_json',
        ];
        $authToken = new PyrusAuthToken('test', 'test', 'test');

        $transport = $this->mock(PyrusTransport::class);
        $transport->expects($this->once())
            ->method('request')
            ->with(
                $this->callback(
                    fn (PyrusRequest $request): bool => $request->method === PyrusEndpoint::AUTH->method()
                        && $request->url === PyrusBaseUrl::ACCOUNTS->value . PyrusEndpoint::AUTH->path()
                        && $request->payload === $normalizedCredentials
                        && [] === $request->headers
                )
            )
            ->willReturn(
                new PyrusResponse(PyrusResponseStatus::OK, $authTokenJson)
            );

        $dataConverter = $this->mock(PyrusDataConverter::class);
        $dataConverter->expects($this->once())
            ->method('normalize')
            ->with(
                $this->identicalTo($credentials)
            )
            ->willReturn($normalizedCredentials);
        $dataConverter->expects($this->once())
            ->method('jsonDecode')
            ->with(
                $this->identicalTo($authTokenJson)
            )
            ->willReturn($authTokenArray);
        $dataConverter->expects($this->once())
            ->method('denormalize')
            ->with(
                $this->identicalTo($authTokenArray),
                $this->identicalTo(PyrusAuthToken::class)
            )
            ->willReturn($authToken);

        $client = new PyrusClientImpl(
            $transport,
            $dataConverter,
            new PyrusClientOptions()
        );
        $res = $client->auth($credentials);

        $this->assertSame($authToken, $res);
    }
}
