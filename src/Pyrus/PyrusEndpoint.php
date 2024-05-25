<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Pyrus;

use SuareSu\PyrusClient\Transport\RequestMethod;

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
    public function method(): RequestMethod
    {
        return match ($this) {
            self::AUTH => RequestMethod::POST,

            self::CATALOG_INDEX, self::CATALOG_READ => RequestMethod::GET,
            self::CATALOG_UPDATE => RequestMethod::POST,
            self::CATALOG_CREATE => RequestMethod::PUT,
        };
    }

    /**
     * Return path string with placeholders substituted by params.
     *
     * @param mixed[] $params
     *
     * @psalm-param scalar[] $params
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

        $stringifiedParams = array_map(
            fn (mixed $item): string => (string) $item,
            $params
        );

        return sprintf($path, ...$stringifiedParams);
    }
}
