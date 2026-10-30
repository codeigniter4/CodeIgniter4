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
use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Validation\ValidationInterface;

/**
 * Console prompt over an `Output`.
 */
class Prompt
{
    private const MAX_LINE_WIDTH = 125;
    private const VISIBLE_ROWS   = 5;

    /**
     * @param resource|null $keys The stream of keypresses read by `select()` and `multiselect()`, or `null` for STDIN
     */
    public function __construct(
        private readonly Output $output,
        private ?ValidationInterface $validation = null,
        private readonly mixed $keys = null,
    ) {
    }

    /**
     * Asks a question and returns the answer, asking again until it passes the validation rules.
     *
     * @param list<int|string>|string|null $options    A default answer, or a list of allowed answers whose first one is the default
     * @param list<string>|string|null     $validation Validation rules
     */
    public function ask(string $field, array|string|null $options = null, array|string|null $validation = null): string
    {
        $rules   = is_string($validation) ? explode('|', $validation) : ($validation ?? []);
        $hint    = '';
        $default = '';

        if (is_string($options)) {
            $hint    = sprintf(' [%s]', $this->output->color($options, 'green'));
            $default = $options;
        } elseif ($options !== null && $options !== []) {
            $default = (string) $options[0];
            $others  = $options;
            unset($others[0]);

            if ($others === []) {
                $hint = $this->output->color($default, 'green');
            } else {
                $hint    = sprintf('[%s, %s]', $this->output->color($default, 'green'), implode(', ', $others));
                $rules[] = sprintf('in_list[%s]', implode(', ', $options));
            }
        }

        // The reader renders the prompt itself, so readline redraws repaint it instead of erasing it.
        $prompt = sprintf('%s%s%s: ', $field, trim($field) !== '' ? ' ' : '', $hint);

        while (true) {
            $input = $this->readLine($prompt);
            $input = $input === '' ? $default : $input;

            if ($rules === [] || $this->validate(sprintf('"%s"', trim($field)), $input, $rules)) {
                $this->output->deferNewLine();

                return $input;
            }

            if ($this->output->getInputOutput()->isAtEnd()) {
                throw CLIException::forInputEnded();
            }
        }
    }

    /**
     * Lists the options by key and returns the chosen key, the first key being the default.
     *
     * @param list<string>|string      $text       The text before the list, or the texts before the list and before the answer
     * @param array<array-key, string> $options
     * @param list<string>|string|null $validation Validation rules
     *
     * @throws InvalidArgumentException
     */
    public function askByKey(array|string $text, array $options, array|string|null $validation = null): string
    {
        if (is_string($text)) {
            $text = [$text];
        }

        $this->assertHasOptions($options);

        $line = array_shift($text);

        if ($line !== null) {
            $this->output->write($line);
        }

        $this->writeKeysAndValues($options);

        return $this->ask(sprintf('%s%s', PHP_EOL, array_shift($text)), array_keys($options), $validation);
    }

    /**
     * Lists the options by key and returns the options chosen by comma-separated keys, the first key being the default.
     *
     * @param array<array-key, string> $options
     *
     * @return array<array-key, string>
     *
     * @throws InvalidArgumentException
     */
    public function askByMultipleKeys(string $text, array $options): array
    {
        $this->assertHasOptions($options);

        $default = (string) array_key_first($options);
        $others  = $options;
        unset($others[$default]);

        $field = $this->output->color($default, 'green');

        if ($others !== []) {
            $field = sprintf(
                'You can specify multiple values separated by commas.%s[%s, %s]',
                PHP_EOL,
                $field,
                implode(', ', array_keys($others)),
            );
        }

        $this->output->write($text);
        $this->writeKeysAndValues($options);
        $this->output->newLine();

        while (true) {
            $input = $this->readLine(sprintf('%s : ', $field));
            $input = $input === '' ? $default : $input;
            $keys  = array_map(intval(...), explode(',', $input));

            if (preg_match('/^\d+(,\d+)*$/', $input) === 1 && array_diff($keys, array_keys($options)) === []) {
                $this->output->deferNewLine();

                return array_intersect_key($options, array_flip($keys));
            }

            $this->output->error('Please select correctly.');

            if ($this->output->getInputOutput()->isAtEnd()) {
                throw CLIException::forInputEnded();
            }

            $this->output->newLine();
        }
    }

    /**
     * Lets the user pick one option with the arrow keys and returns its key, asking for its number where keys cannot be read.
     *
     * @template TKey of array-key
     *
     * @param array<TKey, string> $options
     * @param TKey|null           $default The key highlighted at the start, or `null` for the first one
     *
     * @return TKey
     *
     * @throws InvalidArgumentException
     */
    public function select(string $question, array $options, int|string|null $default = null): int|string
    {
        $this->assertHasOptions($options);

        $keys    = array_keys($options);
        $cursor  = $this->getPositions($options, [$default ?? $keys[0]])[0];
        $default = $keys[$cursor];

        if ($this->isSilenced()) {
            return $default;
        }

        $reader = $this->createKeyReader();

        if ($reader?->open() !== true) {
            return $this->selectByNumber($question, $options, $cursor);
        }

        try {
            $answer = $this->navigate($reader, $question, $options, $cursor, null, false);
        } finally {
            $reader->close();
        }

        // @codeCoverageIgnoreStart
        if ($answer === null) {
            $this->interrupt();

            return $default;
        }
        // @codeCoverageIgnoreEnd

        return $keys[$answer[0]];
    }

    /**
     * Lets the user tick options with the arrow keys and the space bar, asking for their numbers where keys cannot be read.
     *
     * @template TKey of array-key
     *
     * @param array<TKey, string> $options
     * @param list<TKey>          $default The keys ticked at the start
     *
     * @return array<TKey, string> The ticked options, in the order of `$options`
     *
     * @throws CLIException             When a required answer has no default on an output that cannot ask
     * @throws InvalidArgumentException
     */
    public function multiselect(string $question, array $options, array $default = [], bool $required = false): array
    {
        $this->assertHasOptions($options);

        $ticked = $this->getPositions($options, $default);

        if ($this->isSilenced()) {
            if ($required && $ticked === []) {
                throw new CLIException(sprintf('"%s" needs at least one option, but none is ticked by default and the output cannot ask.', $question));
            }

            return $this->pickPositions($options, $ticked);
        }

        $reader = $this->createKeyReader();

        if ($reader?->open() !== true) {
            return $this->multiselectByNumber($question, $options, $ticked, $required);
        }

        try {
            $answer = $this->navigate($reader, $question, $options, 0, $ticked, $required);
        } finally {
            $reader->close();
        }

        // @codeCoverageIgnoreStart
        if ($answer === null) {
            $this->interrupt();

            return $this->pickPositions($options, $ticked);
        }
        // @codeCoverageIgnoreEnd

        return $this->pickPositions($options, $answer[1]);
    }

    /**
     * Checks the answer against the rules, writing the first error when it fails.
     *
     * @param string              $field The label used in the error
     * @param list<string>|string $rules
     */
    protected function validate(string $field, string $value, array|string $rules): bool
    {
        $this->validation ??= single_service('validation');

        $this->validation->reset()->setRules([
            'temp' => ['label' => $field, 'rules' => $rules],
        ]);

        if ($this->validation->run(['temp' => $value])) {
            return true;
        }

        $this->output->error($this->validation->getError('temp'));

        return false;
    }

    /**
     * @param array<array-key, string> $options
     *
     * @throws InvalidArgumentException
     */
    private function assertHasOptions(array $options): void
    {
        if ($options === []) {
            throw new InvalidArgumentException('No options to select from were provided');
        }
    }

    /**
     * Returns the positions of the keys in `$options`, in the order of `$options`.
     *
     * @param array<array-key, string> $options
     * @param list<array-key>          $keys
     *
     * @return list<int>
     *
     * @throws InvalidArgumentException
     */
    private function getPositions(array $options, array $keys): array
    {
        $positions = array_flip(array_map(strval(...), array_keys($options)));
        $found     = [];

        foreach ($keys as $key) {
            if (! array_key_exists((string) $key, $positions)) {
                throw new InvalidArgumentException(sprintf('"%s" is not one of the options.', $key));
            }

            $found[] = $positions[(string) $key];
        }

        $found = array_values(array_unique($found));
        sort($found);

        return $found;
    }

    /**
     * @param array<array-key, string> $options
     * @param list<int>                $positions
     *
     * @return array<array-key, string>
     */
    private function pickPositions(array $options, array $positions): array
    {
        return array_intersect_key($options, array_flip(array_intersect_key(array_keys($options), array_flip($positions))));
    }

    private function isSilenced(): bool
    {
        return $this->output->isQuiet() || $this->output->getInputOutput() instanceof NullInputOutput;
    }

    private function createKeyReader(): ?KeyReader
    {
        if ($this->keys !== null && ! stream_isatty($this->keys)) {
            return new KeyReader($this->keys, false);
        }

        if (! is_cli() || service('environment')->isTesting() || is_windows()) {
            return null;
        }

        // @codeCoverageIgnoreStart
        $stream = $this->keys ?? STDIN;

        if (! stream_isatty($stream) || ! stream_isatty(STDOUT) || ! function_exists('shell_exec')) {
            return null;
        }

        return new KeyReader($stream, true);
        // @codeCoverageIgnoreEnd
    }

    /**
     * Runs the arrow-key menu until the user confirms.
     *
     * @param array<array-key, string> $options
     * @param list<int>|null           $ticked  The ticked positions, or `null` for a single choice
     *
     * @return array{int, list<int>}|null The highlighted and ticked positions, or `null` on Ctrl+C
     */
    private function navigate(KeyReader $reader, string $question, array $options, int $cursor, ?array $ticked, bool $required): ?array
    {
        $labels = array_values($options);
        $count  = count($labels);
        $rows   = max(1, min($count, self::VISIBLE_ROWS, $this->output->getTerminal()->getHeight() - 3));
        $first  = max(0, $cursor - $rows + 1);
        $keys   = $ticked === null
            ? ['↑↓' => 'navigate', '⏎' => 'submit']
            : ['↑↓' => 'navigate', 'space' => 'select', 'a' => 'all', '⏎' => 'submit'];
        $error = '';
        $drawn = 0;

        $this->output->write($question);
        $this->output->writeRaw("\e[?25l");

        try {
            while (true) {
                $drawn = $this->drawMenu($labels, $cursor, $ticked, $first, $rows, $keys, $error, $drawn);
                $error = '';
                $key   = $reader->read();

                if ($key === "\x03") {
                    return null;
                }

                if ($key === '' && $required && $ticked === []) {
                    throw CLIException::forInputEnded();
                }

                if ($key === '') {
                    break;
                }

                if ($key === "\n" || $key === "\r") {
                    if ($required && $ticked === []) {
                        $error = 'Please select at least one option.';

                        continue;
                    }

                    break;
                }

                $cursor = match ($key) {
                    "\e[A", "\eOA", 'k'     => ($cursor + $count - 1) % $count,
                    "\e[B", "\eOB", 'j'     => ($cursor + 1) % $count,
                    "\e[H", "\eOH", "\e[1~" => 0,
                    "\e[F", "\eOF", "\e[4~" => $count - 1,
                    default                 => $cursor,
                };

                if ($ticked !== null && $key === ' ') {
                    $ticked = in_array($cursor, $ticked, true)
                        ? array_values(array_diff($ticked, [$cursor]))
                        : [...$ticked, $cursor];
                } elseif ($ticked !== null && $key === 'a') {
                    $ticked = count($ticked) === $count ? [] : range(0, $count - 1);
                }

                if ($cursor < $first) {
                    $first = $cursor;
                } elseif ($cursor >= $first + $rows) {
                    $first = $cursor - $rows + 1;
                }
            }

            $this->output->deferNewLine();

            return [$cursor, $ticked ?? []];
        } finally {
            $this->output->writeRaw("\e[?25h");
        }
    }

    /**
     * Draws the status line and the visible rows over the previous drawing.
     *
     * @param list<string>          $labels
     * @param list<int>|null        $ticked
     * @param array<string, string> $keys   The actions of the help line, keyed by their keys
     *
     * @return int The number of lines drawn
     */
    private function drawMenu(array $labels, int $cursor, ?array $ticked, int $first, int $rows, array $keys, string $error, int $drawn): int
    {
        $width = $this->output->getTerminal()->getWidth();
        $lines = [$this->formatStatus($keys, $error, $width)];

        foreach (array_slice($labels, $first, $rows, true) as $position => $label) {
            $box  = $ticked === null ? '' : (in_array($position, $ticked, true) ? '◉ ' : '◯ ');
            $line = mb_strimwidth(sprintf('%s %s%s', $position === $cursor ? '❯' : ' ', $box, $label), 0, $width - 1, '...');

            $lines[] = $position === $cursor ? $this->output->color($line, 'green') : $line;
        }

        if ($drawn > 0) {
            $this->output->writeRaw(sprintf("\e[%dA", $drawn));
        }

        foreach ($lines as $line) {
            $this->output->print(sprintf("\r\e[2K%s%s", $line, PHP_EOL));
        }

        return count($lines);
    }

    /**
     * Returns the error, or else the help line, fitted to one row.
     *
     * @param array<string, string> $keys
     */
    private function formatStatus(array $keys, string $error, int $width): string
    {
        $help = implode(' • ', array_map(
            static fn (string $key, string $action): string => sprintf('%s %s', $key, $action),
            array_keys($keys),
            $keys,
        ));

        if ($error !== '' || mb_strwidth($help) >= $width) {
            return $this->output->color(mb_strimwidth($error === '' ? $help : $error, 0, $width - 1, '...'), $error === '' ? 'dark_gray' : 'red');
        }

        return implode($this->output->color(' • ', 'dark_gray'), array_map(
            fn (string $key, string $action): string => sprintf('%s %s', $key, $this->output->color($action, 'dark_gray')),
            array_keys($keys),
            $keys,
        ));
    }

    /**
     * @param array<array-key, string> $options
     */
    private function selectByNumber(string $question, array $options, int $cursor): int|string
    {
        $keys = array_keys($options);

        $this->output->write($question);
        $this->writeKeysAndValues(array_combine(range(1, count($keys)), array_values($options)));

        $default = (string) ($cursor + 1);
        $prompt  = sprintf('Number [%s]: ', $this->output->color($default, 'green'));

        while (true) {
            $answer = $this->readLine($prompt);
            $answer = $answer === '' ? $default : $answer;

            if (preg_match('/^\d+$/', $answer) === 1 && (int) $answer >= 1 && (int) $answer <= count($keys)) {
                $this->output->deferNewLine();

                return $keys[(int) $answer - 1];
            }

            $this->output->error('Please select correctly.');

            if ($this->output->getInputOutput()->isAtEnd()) {
                throw CLIException::forInputEnded();
            }
        }
    }

    /**
     * @param array<array-key, string> $options
     * @param list<int>                $ticked
     *
     * @return array<array-key, string>
     */
    private function multiselectByNumber(string $question, array $options, array $ticked, bool $required): array
    {
        $count   = count($options);
        $default = implode(',', array_map(static fn (int $position): int => $position + 1, $ticked));
        $default = $default === '' && ! $required ? '0' : $default;
        $prompt  = $required ? 'Numbers, separated by commas' : 'Numbers, separated by commas (0 for none)';
        $prompt  = $default === '' ? sprintf('%s: ', $prompt) : sprintf('%s [%s]: ', $prompt, $this->output->color($default, 'green'));

        $this->output->write($question);
        $this->writeKeysAndValues(array_combine(range(1, $count), array_values($options)));

        while (true) {
            $answer = $this->readLine($prompt);
            $answer = $answer === '' ? $default : $answer;

            $numbers = array_map(intval(...), explode(',', $answer));
            $none    = $answer === '0' && ! $required;

            if ($none || (preg_match('/^\d+(\s*,\s*\d+)*$/', $answer) === 1 && min($numbers) >= 1 && max($numbers) <= $count)) {
                $this->output->deferNewLine();

                return $none ? [] : $this->pickPositions($options, array_map(static fn (int $number): int => $number - 1, $numbers));
            }

            $this->output->error($answer === '' || $answer === '0' ? 'Please select at least one option.' : 'Please select correctly.');

            if ($this->output->getInputOutput()->isAtEnd()) {
                throw CLIException::forInputEnded();
            }
        }
    }

    private function readLine(string $prompt): string
    {
        $this->output->writeDeferredNewLine();
        $this->output->setCursorAtLineStart(true);

        return trim($this->output->getInputOutput()->input($prompt));
    }

    /**
     * Re-raises Ctrl+C as SIGINT, or exits with 130 where signals cannot be sent.
     *
     * @codeCoverageIgnore
     */
    private function interrupt(): void
    {
        $this->output->newLine();

        if (function_exists('posix_kill') && function_exists('posix_getpid')) {
            posix_kill(posix_getpid(), defined('SIGINT') ? SIGINT : 2);

            return;
        }

        exit(130);
    }

    /**
     * @param array<array-key, string> $options
     */
    private function writeKeysAndValues(array $options): void
    {
        $padLeft = max(array_map(mb_strwidth(...), array_keys($options))) + 6;
        $width   = min(self::MAX_LINE_WIDTH, $this->output->getTerminal()->getWidth()) - $padLeft;

        foreach ($options as $key => $description) {
            $lines = explode(PHP_EOL, wordwrap($description, $width, PHP_EOL));

            $this->output->write(sprintf(
                '%s%s',
                $this->output->color(str_pad(sprintf('  [%s]  ', $key), $padLeft), 'green'),
                implode(sprintf('%s%s', PHP_EOL, str_repeat(' ', $padLeft)), $lines),
            ));
        }
    }
}
