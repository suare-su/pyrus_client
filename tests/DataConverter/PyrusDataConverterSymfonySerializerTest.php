<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Tests\DataConverter;

use SuareSu\PyrusClient\DataConverter\PyrusDataConverterSymfonySerializer;
use SuareSu\PyrusClient\Exception\PyrusDataConverterException;
use SuareSu\PyrusClient\Tests\BaseCase;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @internal
 */
final class PyrusDataConverterSymfonySerializerTest extends BaseCase
{
    /**
     * @test
     */
    public function testNormalize(): void
    {
        $data = new \stdClass();
        $normalizedData = [
            'test_key' => 'test value',
        ];

        $serializer = $this->mock(Serializer::class);
        $serializer->expects($this->once())
            ->method('normalize')
            ->with(
                $this->identicalTo($data),
                $this->identicalTo(null),
                $this->identicalTo(
                    [
                        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                    ]
                )
            )
            ->willReturn($normalizedData);

        $dataConverter = new PyrusDataConverterSymfonySerializer($serializer);
        $res = $dataConverter->normalize($data);

        $this->assertSame($normalizedData, $res);
    }

    /**
     * @test
     */
    public function testNormalizeException(): void
    {
        $data = new \stdClass();
        $exceptionMessage = 'test message';
        $expectedException = new PyrusDataConverterException($exceptionMessage);

        $serializer = $this->mock(Serializer::class);
        $serializer->expects($this->once())
            ->method('normalize')
            ->willThrowException(new \RuntimeException($exceptionMessage));

        $dataConverter = new PyrusDataConverterSymfonySerializer($serializer);

        $this->expectExceptionObject($expectedException);
        $dataConverter->normalize($data);
    }

    /**
     * @test
     */
    public function testDenormalize(): void
    {
        $data = [
            'test_key' => 'test value',
        ];
        $type = 'test_type';
        $denormalizedData = new \stdClass();

        $serializer = $this->mock(Serializer::class);
        $serializer->expects($this->once())
            ->method('denormalize')
            ->with(
                $this->identicalTo($data),
                $this->identicalTo($type)
            )
            ->willReturn($denormalizedData);

        $dataConverter = new PyrusDataConverterSymfonySerializer($serializer);
        $res = $dataConverter->denormalize($data, $type);

        $this->assertSame($denormalizedData, $res);
    }

    /**
     * @test
     */
    public function testDenormalizeException(): void
    {
        $data = [
            'test_key' => 'test value',
        ];
        $type = 'test_type';
        $exceptionMessage = 'test message';
        $expectedException = new PyrusDataConverterException($exceptionMessage);

        $serializer = $this->mock(Serializer::class);
        $serializer->expects($this->once())
            ->method('denormalize')
            ->willThrowException(new \RuntimeException($exceptionMessage));

        $dataConverter = new PyrusDataConverterSymfonySerializer($serializer);

        $this->expectExceptionObject($expectedException);
        $dataConverter->denormalize($data, $type);
    }

    /**
     * @test
     */
    public function testJsonDecode(): void
    {
        $data = '{"test_key":"test value"}';
        $decodedData = [
            'test_key' => 'test value',
        ];

        $serializer = $this->mock(Serializer::class);

        $dataConverter = new PyrusDataConverterSymfonySerializer($serializer);
        $res = $dataConverter->jsonDecode($data);

        $this->assertSame($decodedData, $res);
    }

    /**
     * @test
     */
    public function testJsonDecodeException(): void
    {
        $data = '{"test_key":';
        $exceptionMessage = 'Syntax error';
        $expectedException = new PyrusDataConverterException($exceptionMessage, 4);

        $serializer = $this->mock(Serializer::class);

        $dataConverter = new PyrusDataConverterSymfonySerializer($serializer);

        $this->expectExceptionObject($expectedException);
        $dataConverter->jsonDecode($data);
    }
}