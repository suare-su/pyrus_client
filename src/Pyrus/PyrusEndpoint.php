<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Pyrus;

use SuareSu\PyrusClient\Transport\PyrusRequestMethod;

/**
 * Descriptions of all endpoints in Pyrus.
 *
 * @psalm-api
 */
enum PyrusEndpoint
{
    case AUTH;

    case CATALOG_INDEX;
    case CATALOG_CREATE;
    case CATALOG_READ;
    case CATALOG_UPDATE;

    case FORM_INDEX;
    case FORM_READ;

    case FORM_TASK_CREATE;
    case FORM_TASK_READ;

    case FILE_UPLOAD;

    case TEST_GET;
    case TEST_POST_PATH_PARAMS;

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

            self::FORM_INDEX => '/forms',
            self::FORM_READ => '/forms/%s',

            self::FORM_TASK_CREATE => '/tasks',
            self::FORM_TASK_READ => '/tasks/%s',

            self::FILE_UPLOAD => '/files/upload',

            self::TEST_GET => '/test',
            self::TEST_POST_PATH_PARAMS => '/test/%s',
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

            self::FORM_INDEX, self::FORM_READ => PyrusRequestMethod::GET,

            self::FORM_TASK_READ => PyrusRequestMethod::GET,
            self::FORM_TASK_CREATE => PyrusRequestMethod::POST,

            self::FILE_UPLOAD => PyrusRequestMethod::POST,

            self::TEST_GET => PyrusRequestMethod::GET,
            self::TEST_POST_PATH_PARAMS => PyrusRequestMethod::POST,
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
        $res = \sprintf($path, ...$params);

        return $res;
    }
}
