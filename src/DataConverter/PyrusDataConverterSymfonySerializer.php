<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\DataConverter;

use SuareSu\PyrusClient\Exception\PyrusDataConverterException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Object that converts data between request/response payload and internal objects.
 */
final class PyrusDataConverterSymfonySerializer implements PyrusDataConverter
{
    public function __construct(private readonly Serializer $serializer)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(array|object $data): array
    {
        try {
            /** @var array<string, mixed> */
            $normalizedData = $this->serializer->normalize(
                data: $data,
                context: [
                    AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                ]
            );
        } catch (\Throwable $e) {
            throw new PyrusDataConverterException($e->getMessage(), (int) $e->getCode(), $e);
        }

        return $normalizedData;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(mixed $data, string $type): object|array
    {
        try {
            /** @var object|array */
            $denormalizedData = $this->serializer->denormalize($data, $type);
        } catch (\Throwable $e) {
            throw new PyrusDataConverterException($e->getMessage(), (int) $e->getCode(), $e);
        }

        return $denormalizedData;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonDecode(string $json): array
    {
        try {
            /** @psalm-var array<string, mixed>|scalar */
            $res = $this->serializer->decode($json, 'json');
        } catch (\Throwable $e) {
            throw new PyrusDataConverterException($e->getMessage(), (int) $e->getCode(), $e);
        }

        return \is_array($res) ? $res : [];
    }
}
