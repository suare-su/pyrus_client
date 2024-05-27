<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Pyrus;

use SuareSu\PyrusClient\Transport\PyrusRequestMethod;

/**
 * Descriptions of all endpoints in Pyrus.
 */
enum PyrusEndpoint
{
    case AUTH;

    case CATALOG_INDEX;
    case CATALOG_CREATE;
    case CATALOG_READ;
    case CATALOG_UPDATE;

    /**
     * Return path related to this enum item.
     *
     * @psalm-return non-empty-string
     */
    private function getInternalPath(): string
    {
        return match ($this) {
            self::AUTH => '/auth',

            self::CATALOG_INDEX, self::CATALOG_CREATE => '/catalogs',
            self::CATALOG_READ, self::CATALOG_UPDATE => '/catalogs/%s',
        };
    }

    /**
     * Return HTTP method required for this endpoint.
     */
    public function method(): PyrusRequestMethod
    {
        return match ($this) {
            self::AUTH => PyrusRequestMethod::POST,

            self::CATALOG_INDEX, self::CATALOG_READ => PyrusRequestMethod::GET,
            self::CATALOG_UPDATE => PyrusRequestMethod::POST,
            self::CATALOG_CREATE => PyrusRequestMethod::PUT,
        };
    }

    /**
     * Return path string with placeholders substituted by params.
     *
     * @param array<float|int|string> $params
     *
     * @psalm-return non-empty-string
     */
    public function path(array $params = []): string
    {
        $path = $this->getInternalPath();

        preg_match_all('/%s/', $path, $matches);
        $countParams = \count($params);
        $countMatches = \count($matches[0]);
        if ($countParams !== $countMatches) {
            throw new \InvalidArgumentException('Number of params must be equal to a number of placeholders');
        }

        /** @psalm-var non-empty-string */
        $res = sprintf($path, ...$params);

        return $res;
    }
}
