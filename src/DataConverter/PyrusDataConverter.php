<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\DataConverter;

use SuareSu\PyrusClient\Exception\PyrusDataConverterException;

/**
 * Object that converts data between request/response payload and internal objects.
 */
interface PyrusDataConverter
{
    /**
     * Converts objects to data that can be used in Pyrus requests.
     *
     * @return array<string, mixed>
     *
     * @throws PyrusDataConverterException
     */
    public function normalize(array|object $data): array;

    /**
     * Converts objects to data that can be used in Pyrus requests.
     *
     * @return object|mixed[]
     *
     * @throws PyrusDataConverterException
     */
    public function denormalize(mixed $data, string $type): object|array;

    /**
     * Convert json text to an associative array.
     *
     * @return array<string, mixed>
     *
     * @throws PyrusDataConverterException
     */
    public function jsonDecode(string $json): array;
}
