<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Tests\Transport;

use SuareSu\PyrusClient\Client\PyrusClientOptions;
use SuareSu\PyrusClient\Tests\BaseCase;
use SuareSu\PyrusClient\Transport\PyrusRequest;
use SuareSu\PyrusClient\Transport\PyrusRequestMethod;
use SuareSu\PyrusClient\Transport\PyrusSymfonyHttpTransport;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 */
final class PyrusSymfonyHttpTransportTest extends BaseCase
{
    /**
     * @dataProvider provideRequest
     */
    public function testRequest(PyrusRequest $request, ?PyrusClientOptions $requestOptions, array $symfonyOptions, int $statusCode, string $content): void
    {
        $symfonyResponse = $this->mock(ResponseInterface::class);
        $symfonyResponse->expects($this->atLeastOnce())->method('getStatusCode')->willReturn($statusCode);
        $symfonyResponse->expects($this->atLeastOnce())->method('getContent')->willReturn($content);

        $symfonyTransport = $this->mock(HttpClientInterface::class);
        $symfonyTransport->expects($this->once())
            ->method('request')
            ->with(
                $this->identicalTo($request->method->value),
                $this->identicalTo($request->url),
                $this->identicalTo($symfonyOptions)
            )
            ->willReturn($symfonyResponse);

        $transport = new PyrusSymfonyHttpTransport($symfonyTransport);
        $response = $transport->request($request, $requestOptions);

        $this->assertSame($statusCode, $response->status->value);
        $this->assertSame($content, $response->payload);
    }

    public static function provideRequest(): array
    {
        $url = 'http://test.get';
        $payload = [
            'payload_param' => 'payload value',
        ];
        $headers = [
            'header_param' => 'header value',
        ];

        return [
            'get request' => [
                new PyrusRequest(PyrusRequestMethod::GET, $url, $payload, $headers),
                null,
                [
                    'headers' => $headers,
                    'query' => $payload,
                ],
                200,
                'test content',
            ],
            'get request with options' => [
                new PyrusRequest(PyrusRequestMethod::GET, $url, $payload, $headers),
                new PyrusClientOptions(),
                [
                    'headers' => $headers,
                    'query' => $payload,
                    'max_duration' => PyrusClientOptions::DEFAULT_TIMEOUT,
                    'retry_failed' => [
                        'max_retries' => PyrusClientOptions::DEFAULT_MAX_RETRIES,
                        'delay' => PyrusClientOptions::DEFAULT_RETRY_DELAY,
                    ],
                ],
                200,
                'test content',
            ],
            'post request' => [
                new PyrusRequest(PyrusRequestMethod::POST, $url, $payload),
                null,
                [
                    'body' => $payload,
                ],
                200,
                'test content',
            ],
        ];
    }
}
