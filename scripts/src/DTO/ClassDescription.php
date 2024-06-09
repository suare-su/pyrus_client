<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Scripts\DTO;

use Symfony\Component\PropertyInfo\Type;

/**
 * DTO that stores properties and types information about class.
 *
 * @internal
 *
 * @psalm-api
 */
final class ClassDescription
{
    public function __construct(
        /** @psalm-var class-string */
        public readonly string $className,
        /** @psalm-var string */
        public readonly string $shortClassName,
        /** @var array<string, Type[]> */
        public readonly array $properties,
    ) {
    }
}
