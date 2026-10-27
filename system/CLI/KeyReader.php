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
 * Reader of single keypresses from a stream.
 *
 * @internal
 */
final class KeyReader
{
    private string $savedMode = '';

    /**
     * @param resource $stream
     * @param bool     $raw    Whether to switch the terminal to raw mode while open
     */
    public function __construct(
        private readonly mixed $stream,
        private readonly bool $raw,
    ) {
    }

    /**
     * Returns `false` when raw mode was wanted but the terminal settings could not be read.
     */
    public function open(): bool
    {
        if (! $this->raw) {
            return true;
        }

        // @codeCoverageIgnoreStart
        $this->savedMode = trim((string) shell_exec('stty -g < /dev/tty 2>/dev/null'));

        if ($this->savedMode === '') {
            return false;
        }

        // Signals stay off so Ctrl+C arrives as a key, letting the terminal be restored before the process ends.
        shell_exec('stty -icanon -isig -echo < /dev/tty 2>/dev/null');

        return true;
        // @codeCoverageIgnoreEnd
    }

    public function close(): void
    {
        if ($this->savedMode === '') {
            return;
        }

        // @codeCoverageIgnoreStart
        shell_exec(sprintf('stty %s < /dev/tty 2>/dev/null', escapeshellarg($this->savedMode)));

        $this->savedMode = '';
        // @codeCoverageIgnoreEnd
    }

    /**
     * Returns the next key, or an empty string at the end of the stream.
     */
    public function read(): string
    {
        $key = $this->readByte();

        if ($key !== "\e") {
            return $key;
        }

        $key .= $this->readByte();

        if ($key === "\eO") {
            return $key . $this->readByte();
        }

        if ($key !== "\e[") {
            return $key;
        }

        do {
            $byte = $this->readByte();
            $key .= $byte;
        } while ($byte !== '' && preg_match('/[A-Za-z~]/', $byte) !== 1);

        return $key;
    }

    private function readByte(): string
    {
        $byte = fread($this->stream, 1);

        return $byte === false ? '' : $byte;
    }
}
