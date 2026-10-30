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
 * Writer for the console, targeting STDOUT by default and STDERR for errors.
 */
class Output
{
    /**
     * Whether the last text ended in a line break, so the next `write()` needs none before it.
     */
    private bool $cursorAtLineStart = false;

    /**
     * Whether the STDOUT writes go to STDERR instead, as in the copy made by `forStderr()`.
     */
    private bool $toStderr = false;

    private bool $newLineDeferred = false;

    /**
     * The output that a `forStderr()` copy was made from, which holds the deferred blank line for both.
     */
    private ?self $original = null;

    /**
     * Colour support per stream id, detected on first use.
     *
     * @var array<int, bool>
     */
    private array $colorSupport = [];

    private Verbosity $verbosity = Verbosity::Normal;

    public function __construct(
        private InputOutput $io = new InputOutput(),
        private readonly Terminal $terminal = new Terminal(),
    ) {
        // @codeCoverageIgnoreStart
        if (! defined('STDOUT')) {
            // STDOUT is undefined outside the CLI SAPI.
            define('STDOUT', 'php://output');
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Writes the text as it is, leaving the cursor where the text ends.
     *
     * @param Verbosity $verbosity The lowest level that shows the text.
     */
    public function print(string $text = '', string $foreground = '', string $background = '', Verbosity $verbosity = Verbosity::Normal): void
    {
        if (! $this->accepts($verbosity, $this->toStderr)) {
            return;
        }

        $this->writeDeferredNewLine();
        $this->io->fwrite($this->stream(), $this->color($text, $foreground, $background));

        // A line feed ends both LF and CRLF, so this holds on every OS.
        $this->cursorAtLineStart = str_ends_with($text, "\n");
    }

    /**
     * Writes the text on its own line.
     *
     * @param Verbosity $verbosity The lowest level that shows the text.
     */
    public function write(string $text = '', string $foreground = '', string $background = '', Verbosity $verbosity = Verbosity::Normal): void
    {
        if (! $this->accepts($verbosity, $this->toStderr)) {
            return;
        }

        $this->io->fwrite($this->stream(), $this->line($this->color($text, $foreground, $background)));
    }

    /**
     * Writes the text on its own line to STDERR, or to STDOUT outside the CLI.
     */
    public function error(string $text, string $foreground = 'light_red', string $background = ''): void
    {
        if (! $this->accepts(Verbosity::Normal, true)) {
            return;
        }

        $this->io->fwrite(
            is_cli() ? STDERR : STDOUT,
            $this->line($this->forStderr()->color($text, $foreground, $background)),
        );
    }

    /**
     * @param positive-int $num       The number of blank lines.
     * @param Verbosity    $verbosity The lowest level that shows the lines.
     */
    public function newLine(int $num = 1, Verbosity $verbosity = Verbosity::Normal): void
    {
        for ($i = 0; $i < $num; $i++) {
            $this->write(verbosity: $verbosity);
        }
    }

    /**
     * Writes a blank line before whatever is written next, and none if nothing follows.
     */
    public function deferNewLine(): void
    {
        $this->getDeferralHolder()->newLineDeferred = true;
    }

    /**
     * Writes the blank line held by `deferNewLine()`, if any.
     *
     * @internal
     */
    public function writeDeferredNewLine(): void
    {
        if (! $this->getDeferralHolder()->newLineDeferred) {
            return;
        }

        $this->getDeferralHolder()->newLineDeferred = false;

        $this->writeRaw(PHP_EOL);
        $this->cursorAtLineStart = true;
    }

    /**
     * @param positive-int $num The number of bells.
     */
    public function beep(int $num = 1): void
    {
        $this->writeRaw(str_repeat("\x07", $num));
    }

    /**
     * Clears the screen with a CSI sequence, or with blank lines on a Windows console without VT100 support.
     */
    public function clearScreen(): void
    {
        if (is_windows() && is_cli() && ! $this->terminal->streamSupports('sapi_windows_vt100_support', $this->stream())) {
            $this->newLine(40);

            return;
        }

        $this->writeRaw("\033[H\033[2J");
    }

    /**
     * Writes the text to the target stream without colouring it or touching the cursor state.
     *
     * @internal
     */
    public function writeRaw(string $text): void
    {
        if (! $this->accepts(Verbosity::Normal, $this->toStderr)) {
            return;
        }

        $this->io->fwrite($this->stream(), $text);
    }

    /**
     * Returns the text coloured for the target stream, or unchanged when the stream does not support colours.
     *
     * @param string $format Only `underline` is understood.
     */
    public function color(string $text, string $foreground, string $background = '', string $format = ''): string
    {
        return $this->isDecorated() ? Color::apply($text, $foreground, $background, $format) : $text;
    }

    /**
     * Returns the text wrapped at the terminal width, or at `$max` when narrower,
     * with every line after the first indented by `$padLeft` spaces.
     */
    public function wrap(string $text, int $max = 0, int $padLeft = 0): string
    {
        $width = $this->terminal->getWidth();
        $max   = ($max === 0 ? $width : min($max, $width)) - $padLeft;

        return implode(PHP_EOL . str_repeat(' ', $padLeft), explode(PHP_EOL, wordwrap($text, $max, PHP_EOL)));
    }

    /**
     * Whether the target stream supports colours.
     */
    public function isDecorated(): bool
    {
        return $this->supportsColor($this->stream());
    }

    /**
     * Returns a copy whose STDOUT writes target STDERR, sharing the blank line held by `deferNewLine()`.
     */
    public function forStderr(): static
    {
        $stderrOutput = clone $this;

        $stderrOutput->toStderr = true;
        $stderrOutput->original = $this->getDeferralHolder();

        return $stderrOutput;
    }

    public function getTerminal(): Terminal
    {
        return $this->terminal;
    }

    public function getVerbosity(): Verbosity
    {
        return $this->verbosity;
    }

    public function setVerbosity(Verbosity $verbosity): void
    {
        $this->verbosity = $verbosity;
    }

    /**
     * Whether the level is quiet or silent.
     */
    public function isQuiet(): bool
    {
        return $this->verbosity->value <= Verbosity::Quiet->value;
    }

    /**
     * Whether the level is verbose or above.
     */
    public function isVerbose(): bool
    {
        return $this->verbosity->value >= Verbosity::Verbose->value;
    }

    /**
     * Whether the level is very verbose or above.
     */
    public function isVeryVerbose(): bool
    {
        return $this->verbosity->value >= Verbosity::VeryVerbose->value;
    }

    public function isDebug(): bool
    {
        return $this->verbosity === Verbosity::Debug;
    }

    /**
     * @internal
     */
    public function getInputOutput(): InputOutput
    {
        return $this->io;
    }

    /**
     * @internal
     */
    public function setInputOutput(InputOutput $io): void
    {
        $this->io = $io;
    }

    /**
     * @internal
     */
    public function setCursorAtLineStart(bool $cursorAtLineStart): void
    {
        $this->cursorAtLineStart = $cursorAtLineStart;
    }

    /**
     * Returns the text on a fresh line and marks the cursor as being at the start of the next one.
     */
    private function line(string $text): string
    {
        $prefix = $this->cursorAtLineStart && ! $this->getDeferralHolder()->newLineDeferred ? '' : PHP_EOL;

        $this->cursorAtLineStart = true;

        $this->getDeferralHolder()->newLineDeferred = false;

        return $prefix . $text . PHP_EOL;
    }

    private function getDeferralHolder(): self
    {
        return $this->original ?? $this;
    }

    /**
     * Whether the level shows text written at the given verbosity, where STDERR is never quieter than normal.
     */
    private function accepts(Verbosity $verbosity, bool $toStderr): bool
    {
        if ($this->verbosity === Verbosity::Silent) {
            return false;
        }

        $level = $toStderr ? max($this->verbosity->value, Verbosity::Normal->value) : $this->verbosity->value;

        return $verbosity->value <= $level;
    }

    /**
     * @param resource $stream
     */
    private function supportsColor($stream): bool
    {
        if (! is_cli()) {
            return false;
        }

        $id = get_resource_id($stream);

        $this->colorSupport[$id] ??= $this->terminal->hasColorSupport($stream);

        return $this->colorSupport[$id];
    }

    /**
     * @return resource
     */
    private function stream()
    {
        // Outside the CLI, STDERR is undefined and the transport echoes whatever the handle is.
        return $this->toStderr && is_cli() ? STDERR : STDOUT;
    }
}
