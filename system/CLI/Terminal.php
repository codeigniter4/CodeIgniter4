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

use Throwable;

/**
 * The capabilities and dimensions of the terminal behind the standard streams.
 */
class Terminal
{
    /**
     * Rows reported by the terminal, or `0` when it reports none.
     *
     * @var non-negative-int
     */
    private int $height = 0;

    /**
     * Columns reported by the terminal, or `0` when it reports none.
     *
     * @var non-negative-int
     */
    private int $width = 0;

    private bool $detected = false;

    /**
     * Returns the number of columns, or the default when the terminal does not report them.
     */
    public function getWidth(int $default = 80): int
    {
        if (! $this->detected) {
            $this->generateDimensions();
        }

        return $this->width === 0 ? $default : $this->width;
    }

    /**
     * Returns the number of rows, or the default when the terminal does not report them.
     */
    public function getHeight(int $default = 32): int
    {
        if (! $this->detected) {
            $this->generateDimensions();
        }

        return $this->height === 0 ? $default : $this->height;
    }

    /**
     * Reads the dimensions from the terminal, logging the error and keeping the defaults when that fails.
     */
    public function generateDimensions(): void
    {
        $this->detected = true;

        try {
            [$this->height, $this->width] = is_windows() ? $this->windowsDimensions() : $this->unixDimensions();
        } catch (Throwable $e) {
            $this->height = 0;
            $this->width  = 0;

            log_message('error', (string) $e);
        }
    }

    /**
     * Whether the stream supports the given capability function, such as `stream_isatty`.
     *
     * @param resource $stream
     */
    public function streamSupports(string $function, $stream): bool
    {
        if (service('environment')->isTesting()) {
            return function_exists($function);
        }

        return function_exists($function) && @$function($stream); // @codeCoverageIgnore
    }

    /**
     * Whether the stream supports colours, following https://no-color.org/ and the Windows console emulators.
     *
     * @param resource $stream
     */
    public function hasColorSupport($stream): bool
    {
        if ($this->hasEnv('NO_COLOR')) {
            return false;
        }

        if (getenv('TERM_PROGRAM') === 'Hyper') {
            return true;
        }

        if (is_windows()) {
            return $this->streamSupports('sapi_windows_vt100_support', $stream)
                || $this->hasEnv('ANSICON')
                || getenv('ConEmuANSI') === 'ON'
                || getenv('TERM') === 'xterm';
        }

        return $this->streamSupports('stream_isatty', $stream);
    }

    /**
     * Whether the variable is set in the live `$_SERVER` or the process environment.
     */
    private function hasEnv(string $name): bool
    {
        return isset($_SERVER[$name]) || getenv($name) !== false; // @phpstan-ignore codeigniter.superglobalsOffsetAccess (reads live $_SERVER, not the snapshot service)
    }

    /**
     * @return array{non-negative-int, non-negative-int}
     */
    private function unixDimensions(): array
    {
        $size = exec('stty size 2>/dev/null');

        if ($size !== false && preg_match('/(\d+)\s+(\d+)/', $size, $matches) === 1) {
            return [(int) $matches[1], (int) $matches[2]];
        }

        return [(int) exec('tput lines 2>/dev/null'), (int) exec('tput cols 2>/dev/null')];
    }

    /**
     * Shells such as Cygwin and Git Bash answer `mode CON` wrongly, so a shell variable or `TERM` selects `tput`.
     *
     * @return array{non-negative-int, non-negative-int}
     *
     * @codeCoverageIgnore
     */
    private function windowsDimensions(): array
    {
        $term  = getenv('TERM');
        $shell = getenv('SHELL');

        if (($term !== false && $term !== '') || ($shell !== false && preg_match('/(?:bash|zsh)(?:\.exe)?$/', $shell) === 1)) {
            return [(int) exec('tput lines'), (int) exec('tput cols')];
        }

        $output = [];
        $status = -1;

        exec('mode CON', $output, $status);

        if ($status === 0 && $output !== [] && preg_match('/:\s*(\d+)\n[^:]+:\s*(\d+)\n/', implode("\n", $output), $matches) === 1) {
            return [(int) $matches[1], (int) $matches[2]];
        }

        return [0, 0];
    }
}
