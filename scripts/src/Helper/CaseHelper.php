<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Scripts\Helper;

/**
 * Helper that can convert case notation for variables and methods names.
 *
 * @internal
 *
 * @psalm-api
 */
final class CaseHelper
{
    private function __construct()
    {
    }

    public static function camelToSnake(string $camelCase): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $camelCase));
    }
}
