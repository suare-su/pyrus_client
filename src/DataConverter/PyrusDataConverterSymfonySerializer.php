<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\DataConverter;

use SuareSu\PyrusClient\Exception\PyrusDataConverterException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Object that converts data between request/response payload and internal objects.
 */
final class PyrusDataConverterSymfonySerializer implements PyrusDataConverter
{
    public function __construct(
        private readonly NormalizerInterface $normalizer,
        private readonly DenormalizerInterface $denormalizer
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(array|object $data): array
    {
        try {
            /** @var array<string, mixed> */
            $normalizedData = $this->normalizer->normalize(
                $data,
                null,
                [
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
            $denormalizedData = $this->denormalizer->denormalize($data, $type);
        } catch (\Throwable $e) {
            throw new PyrusDataConverterException($e->getMessage(), (int) $e->getCode(), $e);
        }

        return $denormalizedData;
    }
}
