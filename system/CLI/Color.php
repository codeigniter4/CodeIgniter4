<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\CLI;

use CodeIgniter\CLI\Exceptions\CLIException;

/**
 * ANSI colour codes for terminal text.
 */
final class Color
{
    private const FOREGROUND = [
        'black'        => '0;30',
        'dark_gray'    => '1;30',
        'blue'         => '0;34',
        'dark_blue'    => '0;34',
        'light_blue'   => '1;34',
        'green'        => '0;32',
        'light_green'  => '1;32',
        'cyan'         => '0;36',
        'light_cyan'   => '1;36',
        'red'          => '0;31',
        'light_red'    => '1;31',
        'purple'       => '0;35',
        'light_purple' => '1;35',
        'yellow'       => '0;33',
        'light_yellow' => '1;33',
        'light_gray'   => '0;37',
        'white'        => '1;37',
    ];
    private const BACKGROUND = [
        'black'      => '40',
        'red'        => '41',
        'green'      => '42',
        'yellow'     => '43',
        'blue'       => '44',
        'magenta'    => '45',
        'cyan'       => '46',
        'light_gray' => '47',
    ];
    private const RESET     = "\033[0m";
    private const UNDERLINE = "\033[4m";

    /**
     * Text already coloured by an earlier `apply()`, which keeps its own codes.
     */
    private const COLORED_SEGMENT = '/(\033\[[0-9;]*m.+?\033\[0m)/su';

    /**
     * Wraps the text in the codes for the given colours, leaving already coloured segments as they are.
     *
     * @param string $foreground A colour name, or `''` for no foreground code.
     * @param string $format     Only `underline` is understood.
     *
     * @throws CLIException
     */
    public static function apply(string $text, string $foreground, string $background = '', string $format = ''): string
    {
        if ($text === '') {
            return '';
        }

        if ($foreground !== '' && ! isset(self::FOREGROUND[$foreground])) {
            throw CLIException::forInvalidColor('foreground', $foreground);
        }

        if ($background !== '' && ! isset(self::BACKGROUND[$background])) {
            throw CLIException::forInvalidColor('background', $background);
        }

        $codes = sprintf(
            '%s%s%s',
            $foreground !== '' ? sprintf("\033[%sm", self::FOREGROUND[$foreground]) : '',
            $background !== '' ? sprintf("\033[%sm", self::BACKGROUND[$background]) : '',
            $format === 'underline' ? self::UNDERLINE : '',
        );

        if ($codes === '') {
            return $text;
        }

        $pieces = preg_split(self::COLORED_SEGMENT, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $output = '';

        foreach ($pieces === false ? [$text] : $pieces as $index => $piece) {
            if ($piece === '') {
                continue;
            }

            $output .= $index % 2 === 1 ? $piece : $codes . $piece . self::RESET;
        }

        return $output;
    }

    /**
     * Returns the display width of the text without its colour codes.
     */
    public static function width(string $string): int
    {
        return mb_strwidth((string) preg_replace('/\033\[[0-9;]*m/', '', $string));
    }
}
