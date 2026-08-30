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

/**
 * Input and Output for CLI.
 */
class InputOutput
{
    /**
     * Is the readline library on the system?
     */
    private readonly bool $readlineSupport;

    public function __construct()
    {
        // Readline is an extension for PHP that makes interactivity with PHP
        // much more bash-like.
        // http://www.php.net/manual/en/readline.installation.php
        $this->readlineSupport = extension_loaded('readline');
    }

    /**
     * Get input from the shell, using readline or the standard STDIN
     *
     * Named options must be in the following formats:
     * php index.php user -v --v -name=John --name=John
     *
     * @param string|null $prefix You may specify a string with which to prompt the user.
     */
    public function input(?string $prefix = null): string
    {
        // readline() can't be tested.
        if ($this->readlineSupport && ENVIRONMENT !== 'testing') {
            // @codeCoverageIgnoreStart
            // Libedit reports "EditLine wrapper" and mangles the markers, so only GNU readline gets them.
            if ($prefix !== null && ! str_contains(readline_info('library_version'), 'EditLine')) {
                $prefix = $this->markAnsiNonPrinting($prefix);
            }

            return readline($prefix);
            // @codeCoverageIgnoreEnd
        }

        // self:: skips MockInputOutput's fwrite override, whose filter bookkeeping must not nest inside input().
        self::fwrite(STDOUT, $prefix ?? '');

        $input = fgets(fopen('php://stdin', 'rb'));

        if ($input === false) {
            return '';
        }

        return $input;
    }

    /**
     * While the library is intended for use on CLI commands,
     * commands can be called from controllers and elsewhere
     * so we need a way to allow them to still work.
     *
     * For now, just echo the content, but look into a better
     * solution down the road.
     *
     * @param resource $handle
     */
    public function fwrite($handle, string $string): void
    {
        if (! is_cli()) {
            echo $string;

            return;
        }

        fwrite($handle, $string);
    }

    /**
     * Wraps ANSI escape sequences in readline's non-printing markers so line-redraw column accounting skips them.
     */
    private function markAnsiNonPrinting(string $prefix): string
    {
        return preg_replace('/(\e\[[0-9;]*m)/', "\x01\$1\x02", $prefix);
    }
}
