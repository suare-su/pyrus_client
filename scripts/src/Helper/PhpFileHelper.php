<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Scripts\Helper;

/**
 * Helper that can get and return information about php file content.
 *
 * @internal
 *
 * @psalm-api
 */
final class PhpFileHelper
{
    private function __construct()
    {
    }

    /**
     * @return string[]
     *
     * @psalm-return class-string[]
     */
    public static function getFQCNsFromFolder(\SplFileInfo $folder): array
    {
        $classes = [];

        if (!$folder->isDir()) {
            return $classes;
        }

        $directoryIterator = new \RecursiveDirectoryIterator(
            $folder->getRealPath(),
            \RecursiveDirectoryIterator::SKIP_DOTS
        );
        /** @var iterable<\SplFileInfo> */
        $filesIterator = new \RecursiveIteratorIterator(
            $directoryIterator,
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($filesIterator as $file) {
            if ('php' === strtolower($file->getExtension())) {
                $classes = array_merge($classes, self::getFQCNsFromFile($file));
            }
        }

        return $classes;
    }

    /**
     * @return string[]
     *
     * @psalm-return class-string[]
     */
    public static function getFQCNsFromFile(\SplFileInfo $phpFile): array
    {
        $classes = [];
        $tokens = \PhpToken::tokenize(file_get_contents($phpFile->getRealPath()));
        $namespace = '';

        for ($i = 0; $i < \count($tokens); ++$i) {
            if ('T_NAMESPACE' === $tokens[$i]->getTokenName()) {
                for ($j = $i + 1; $j < \count($tokens); ++$j) {
                    if ('T_NAME_QUALIFIED' === $tokens[$j]->getTokenName()) {
                        $namespace = $tokens[$j]->text;
                        break;
                    }
                }
            }
            if ('T_CLASS' === $tokens[$i]->getTokenName()) {
                for ($j = $i + 1; $j < \count($tokens); ++$j) {
                    if ('T_WHITESPACE' === $tokens[$j]->getTokenName()) {
                        continue;
                    }
                    if ('T_STRING' === $tokens[$j]->getTokenName()) {
                        /** @psalm-var class-string */
                        $class = $namespace . '\\' . $tokens[$j]->text;
                        $classes[] = $class;
                    } else {
                        break;
                    }
                }
            }
        }

        return $classes;
    }
}
