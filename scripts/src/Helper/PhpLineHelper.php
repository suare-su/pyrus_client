<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Scripts\Helper;

/**
 * Helper that can generate line for php file.
 *
 * @internal
 */
final class PhpLineHelper
{
    public const TAB = '    ';
    public const NEW_LINE = "\n";
    public const SEMICOLON = ';';
    public const COMMA = ',';

    /**
     * @psalm-suppress UnusedConstructor
     */
    private function __construct()
    {
    }

    /**
     * Creates one line of code.
     */
    public static function line(mixed $input, int $tabCount = 0, bool $semicolon = true): string
    {
        $prefix = str_repeat(self::TAB, $tabCount);

        $input = array_map(
            function (mixed $line) use ($prefix, $semicolon): string {
                $line = $prefix . ((string) $line);
                if ($semicolon) {
                    $line = rtrim($line, self::SEMICOLON) . self::SEMICOLON;
                }

                return $line;
            },
            \is_array($input) ? $input : [$input]
        );

        return implode(self::NEW_LINE, $input);
    }

    /**
     * Creates one line of code.
     */
    public static function skipLine(int $count = 1): string
    {
        return str_repeat(self::NEW_LINE, $count + 1);
    }

    /**
     * Creates return statement.
     */
    public static function return(mixed $input, int $tabCount = 0): string
    {
        return self::line('return ' . ((string) $input), $tabCount, true);
    }

    /**
     * Creates if condition statement.
     */
    public static function if(string $condition, string|array $body = '', int $tabCount = 0): string
    {
        $ifLine = self::line("if ({$condition}) {", $tabCount, false);
        $ifBody = self::line($body, $tabCount + 1, true);
        $ifEnd = self::line('}', $tabCount, false);

        return $ifLine . self::NEW_LINE . $ifBody . self::NEW_LINE . $ifEnd;
    }

    /**
     * Creates array statement.
     *
     * @psalm-suppress MixedAssignment
     */
    public static function array(string|array $body = '', int $tabCount = 0): string
    {
        $body = \is_array($body) ? $body : [$body];

        $lines = [];
        foreach ($body as $item) {
            $line = rtrim(self::line($item, $tabCount + 1, false), self::COMMA);
            $line .= self::COMMA . self::NEW_LINE;
            $lines[] = $line;
        }

        return '[' . self::NEW_LINE . implode('', $lines) . ']' . self::SEMICOLON;
    }

    /**
     * Creates throw exception statement.
     */
    public static function throwException(string $type, string $message = ''): string
    {
        return "throw new {$type}(\"{$message}\")" . self::SEMICOLON;
    }
}
