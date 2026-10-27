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

    public function __construct(
        private readonly Output $output,
        private ?ValidationInterface $validation = null,
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
            $this->output->setCursorAtLineStart(true);

            $input = trim($this->output->getInputOutput()->input($prompt));
            $input = $input === '' ? $default : $input;

            if ($rules === [] || $this->validate(sprintf('"%s"', trim($field)), $input, $rules)) {
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
            $input = $this->ask($field);
            $input = $input === '' ? $default : $input;
            $keys  = array_map(intval(...), explode(',', $input));

            if (preg_match('/^\d+(,\d+)*$/', $input) === 1 && array_diff($keys, array_keys($options)) === []) {
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
