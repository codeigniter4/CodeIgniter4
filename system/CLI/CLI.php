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

use WeakMap;

/**
 * Static facade for console output, prompts and the parsed command line.
 *
 * Portions of this code were initially from the FuelPHP Framework,
 * version 1.7.x, and used here under the MIT license they were
 * originally made available under. Reference: http://fuelphp.com
 */
class CLI
{
    /**
     * The message displayed at prompts.
     */
    protected static string $waitMsg = 'Press any key to continue...';

    /**
     * Has the class already been initialized?
     *
     * @var bool
     */
    protected static $initialized = false;

    /**
     * Foreground color list
     *
     * @var array<string, string>
     *
     * @deprecated 4.8.0 Unused. The codes live in `Color`.
     */
    protected static $foreground_colors = [
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

    /**
     * Background color list
     *
     * @var array<string, string>
     *
     * @deprecated 4.8.0 Unused. The codes live in `Color`.
     */
    protected static $background_colors = [
        'black'      => '40',
        'red'        => '41',
        'green'      => '42',
        'yellow'     => '43',
        'blue'       => '44',
        'magenta'    => '45',
        'cyan'       => '46',
        'light_gray' => '47',
    ];

    /**
     * List of array segments.
     *
     * @var list<string>
     */
    protected static $segments = [];

    /**
     * @var array<string, list<string|null>|string|null>
     */
    protected static $options = [];

    /**
     * Helps track internally whether the last
     * output was a "write" or a "print" to
     * keep the output clean and as expected.
     *
     * @var string|null
     *
     * @deprecated 4.8.0 Unused. The cursor state lives in `Output`.
     */
    protected static $lastWrite;

    /**
     * Height of the CLI window
     *
     * @var int|null
     *
     * @deprecated 4.8.0 Unused. The dimensions live in `Terminal`.
     */
    protected static $height;

    /**
     * Width of the CLI window
     *
     * @var int|null
     *
     * @deprecated 4.8.0 Unused. The dimensions live in `Terminal`.
     */
    protected static $width;

    /**
     * Whether the current stream supports colored output.
     *
     * @var bool
     *
     * @deprecated 4.8.0 Unused. Colour support is detected per stream by `Output`.
     */
    protected static $isColored = false;

    /**
     * Input and Output for CLI.
     *
     * @deprecated 4.8.0 Unused. The transport is held by `Output`.
     */
    protected static ?InputOutput $io = null;

    /**
     * @deprecated 4.8.0 Unused. The target stream lives in `Output`.
     */
    protected static bool $stdoutToStderr = false;

    private static ?Output $output = null;

    /**
     * The progress bar of each output, so a bar in progress survives a temporary swap of the output.
     *
     * @var WeakMap<Output, ProgressBar>|null
     */
    private static ?WeakMap $progressBars = null;

    /**
     * Static "constructor".
     *
     * @return void
     */
    public static function init()
    {
        if (is_cli()) {
            $parser = new CommandLineParser(service('superglobals')->server('argv', []));

            static::$segments = $parser->getArguments();
            static::$options  = $parser->getOptions();

            static::$initialized = true;
        } elseif (! defined('STDOUT')) {
            // If the command is being called from a controller we need to define STDOUT ourselves
            // For "! defined('STDOUT')" see: https://github.com/codeigniter4/CodeIgniter4/issues/7047
            define('STDOUT', 'php://output'); // @codeCoverageIgnore
        }

        self::resetOutput();
    }

    /**
     * Returns the terminal of the current output.
     */
    public static function getTerminal(): Terminal
    {
        return static::getOutput()->getTerminal();
    }

    /**
     * Returns the writer behind every output method of this facade.
     */
    public static function getOutput(): Output
    {
        self::$output ??= new Output();

        return self::$output;
    }

    /**
     * Replaces the writer behind every output method of this facade.
     */
    public static function setOutput(Output $output): void
    {
        self::$output = $output;
    }

    /**
     * Returns a prompt reading through the current output.
     */
    public static function getPrompt(): Prompt
    {
        return new Prompt(static::getOutput());
    }

    /**
     * Get input from the shell, using readline or the standard STDIN
     *
     * Named options must be in the following formats:
     * php index.php user -v --v -name=John --name=John
     *
     * @param string|null $prefix You may specify a string with which to prompt the user.
     */
    public static function input(?string $prefix = null): string
    {
        return static::getOutput()->getInputOutput()->input($prefix);
    }

    /**
     * Asks the user for input.
     *
     * Usage:
     *
     * // Takes any input
     * $color = CLI::prompt('What is your favorite color?');
     *
     * // Takes any input, but offers default
     * $color = CLI::prompt('What is your favourite color?', 'white');
     *
     * // Will validate options with the in_list rule and accept only if one of the list
     * $color = CLI::prompt('What is your favourite color?', array('red','blue'));
     *
     * // Do not provide options but requires a valid email
     * $email = CLI::prompt('What is your email?', null, 'required|valid_email');
     *
     * @param string                   $field      Output "field" question
     * @param list<int|string>|string  $options    String to a default value, array to a list of options (the first option will be the default value)
     * @param list<string>|string|null $validation Validation rules
     *
     * @return string The user input
     */
    public static function prompt(string $field, $options = null, $validation = null): string
    {
        return static::getPrompt()->ask($field, $options, $validation);
    }

    /**
     * prompt(), but based on the option's key
     *
     * @param list<string>|string      $text       Output "field" text or an one or two value array where the first value is the text before listing the options
     *                                             and the second value the text before asking to select one option. Provide empty string to omit
     * @param array<array-key, string> $options    A list of options (array(key => description)), the first option will be the default value
     * @param list<string>|string|null $validation Validation rules
     *
     * @return string The selected key of $options
     */
    public static function promptByKey($text, array $options, $validation = null): string
    {
        return static::getPrompt()->askByKey($text, $options, $validation);
    }

    /**
     * This method is the same as promptByKey(), but this method supports multiple keys, separated by commas.
     *
     * @param string                   $text    Output "field" text or an one or two value array where the first value is the text before listing the options
     *                                          and the second value the text before asking to select one option. Provide empty string to omit
     * @param array<array-key, string> $options A list of options (array(key => description)), the first option will be the default value
     *
     * @return array<array-key, string> The selected key(s) and value(s) of $options
     */
    public static function promptByMultipleKeys(string $text, array $options): array
    {
        return static::getPrompt()->askByMultipleKeys($text, $options);
    }

    /**
     * Validate one prompt "field" at a time
     *
     * @deprecated 4.8.0 Override `Prompt::validate()` instead.
     *
     * @param string              $field Prompt "field" output
     * @param string              $value Input value
     * @param list<string>|string $rules Validation rules
     */
    protected static function validate(string $field, string $value, $rules): bool
    {
        @trigger_error(sprintf(
            'Since v4.8.0, "%s()" is deprecated. Override "%s::validate()" instead.',
            __METHOD__,
            Prompt::class,
        ), E_USER_DEPRECATED);

        $label      = $field;
        $field      = 'temp';
        $validation = service('validation', null, false);
        $validation->setRules([
            $field => [
                'label' => $label,
                'rules' => $rules,
            ],
        ]);
        $validation->run([$field => $value]);

        if ($validation->hasError($field)) {
            static::error($validation->getError($field));

            return false;
        }

        return true;
    }

    /**
     * Outputs a string to the CLI without any surrounding newlines.
     * Useful for showing repeating elements on a single line.
     *
     * @return void
     */
    public static function print(string $text = '', ?string $foreground = null, ?string $background = null)
    {
        static::getOutput()->print($text, $foreground ?? '', $background ?? '');
    }

    /**
     * Outputs a string to the cli on its own line.
     *
     * @return void
     */
    public static function write(string $text = '', ?string $foreground = null, ?string $background = null)
    {
        static::getOutput()->write($text, $foreground ?? '', $background ?? '');
    }

    /**
     * Outputs an error to the CLI using STDERR instead of STDOUT
     *
     * @return void
     */
    public static function error(string $text, string $foreground = 'light_red', ?string $background = null)
    {
        static::getOutput()->error($text, $foreground, $background ?? '');
    }

    /**
     * Beeps a certain number of times.
     *
     * @param int $num The number of times to beep
     *
     * @return void
     */
    public static function beep(int $num = 1)
    {
        static::getOutput()->beep($num);
    }

    /**
     * Waits a certain number of seconds, optionally showing a wait message and
     * waiting for a key press.
     *
     * @param int  $seconds   Number of seconds
     * @param bool $countdown Show a countdown or not
     *
     * @return void
     */
    public static function wait(int $seconds, bool $countdown = false)
    {
        if ($countdown) {
            $time = $seconds;

            while ($time > 0) {
                static::fwrite(STDOUT, $time . '... ');
                sleep(1);
                $time--;
            }

            static::write();
        } elseif ($seconds > 0) {
            sleep($seconds);
        } else {
            static::write(static::$waitMsg);
            static::getOutput()->getInputOutput()->input();
        }
    }

    /**
     * Enter a number of empty lines
     *
     * @return void
     */
    public static function newLine(int $num = 1)
    {
        for ($i = 0; $i < $num; $i++) {
            static::write();
        }
    }

    /**
     * Clears the screen of output
     *
     * @return void
     */
    public static function clearScreen()
    {
        if (is_windows() && is_cli() && ! static::streamSupports('sapi_windows_vt100_support', STDOUT)) {
            static::newLine(40);

            return;
        }

        static::fwrite(STDOUT, "\033[H\033[2J");
    }

    /**
     * Returns the given text with the correct color codes for a foreground and
     * optionally a background color.
     *
     * @param string      $text       The text to color
     * @param string      $foreground The foreground color
     * @param string|null $background The background color
     * @param string|null $format     Other formatting to apply. Currently only 'underline' is understood
     *
     * @return string The color coded string
     */
    public static function color(string $text, string $foreground, ?string $background = null, ?string $format = null): string
    {
        return static::getOutput()->color($text, $foreground, $background ?? '', $format ?? '');
    }

    /**
     * Get the number of characters in string having encoded characters
     * and ignores styles set by the color() function
     */
    public static function strlen(?string $string): int
    {
        return Color::width((string) $string);
    }

    /**
     * Checks whether the current stream resource supports or
     * refers to a valid terminal type device.
     *
     * @param resource $resource
     */
    public static function streamSupports(string $function, $resource): bool
    {
        return static::getTerminal()->streamSupports($function, $resource);
    }

    /**
     * Returns true if the stream resource supports colors.
     *
     * @param resource $resource
     */
    public static function hasColorSupport($resource): bool
    {
        return static::getTerminal()->hasColorSupport($resource);
    }

    /**
     * Attempts to determine the width of the viewable CLI window.
     */
    public static function getWidth(int $default = 80): int
    {
        return static::getTerminal()->getWidth($default);
    }

    /**
     * Attempts to determine the height of the viewable CLI window.
     */
    public static function getHeight(int $default = 32): int
    {
        return static::getTerminal()->getHeight($default);
    }

    /**
     * Populates the CLI's dimensions.
     *
     * @return void
     */
    public static function generateDimensions()
    {
        static::getTerminal()->generateDimensions();
    }

    /**
     * Displays a progress bar on the CLI. You must call it repeatedly
     * to update it. Set $thisStep = false to erase the progress bar.
     *
     * @param false|float|int $thisStep
     *
     * @return void
     */
    public static function showProgress($thisStep = 1, int $totalSteps = 10)
    {
        $progressBar = self::getProgressBar(static::getOutput());

        if ($thisStep === false) {
            $progressBar->clear();

            return;
        }

        $progressBar->advanceTo(abs($thisStep), max(1, $totalSteps));
    }

    /**
     * Takes a string and writes it to the command line, wrapping to a maximum
     * width. If no maximum width is specified, will wrap to the window's max
     * width.
     *
     * If an int is passed into $pad_left, then all strings after the first
     * will pad with that many spaces to the left. Useful when printing
     * short descriptions that need to start on an existing line.
     */
    public static function wrap(?string $string = null, int $max = 0, int $padLeft = 0): string
    {
        if ((string) $string === '') {
            return '';
        }

        $width = static::getWidth();
        $max   = ($max === 0 ? $width : min($max, $width)) - $padLeft;

        $lines = wordwrap($string, $max, PHP_EOL);

        if ($padLeft > 0) {
            $lines = explode(PHP_EOL, $lines);

            $first = true;

            array_walk($lines, static function (&$line) use ($padLeft, &$first): void {
                if (! $first) {
                    $line = str_repeat(' ', $padLeft) . $line;
                } else {
                    $first = false;
                }
            });

            $lines = implode(PHP_EOL, $lines);
        }

        return $lines;
    }

    // --------------------------------------------------------------------
    // Command-Line 'URI' support
    // --------------------------------------------------------------------

    /**
     * Parses the command line it was called from and collects all
     * options and valid segments.
     *
     * @deprecated 4.8.0 No longer used.
     *
     * @return void
     */
    protected static function parseCommandLine()
    {
        @trigger_error(sprintf('The static method %s() is deprecated and no longer used.', __METHOD__), E_USER_DEPRECATED);

        $args = $_SERVER['argv'] ?? []; // @phpstan-ignore codeigniter.superglobalsOffsetAccess (reads live $_SERVER, not the snapshot service)
        array_shift($args); // scrap invoking program
        $optionValue = false;

        foreach ($args as $i => $arg) {
            // If there's no "-" at the beginning, then
            // this is probably an argument or an option value
            if (mb_strpos($arg, '-') !== 0) {
                if ($optionValue) {
                    // We have already included this in the previous
                    // iteration, so reset this flag
                    $optionValue = false;
                } else {
                    // Yup, it's a segment
                    static::$segments[] = $arg;
                }

                continue;
            }

            $arg   = ltrim($arg, '-');
            $value = null;

            if (isset($args[$i + 1]) && mb_strpos($args[$i + 1], '-') !== 0) {
                $value       = $args[$i + 1];
                $optionValue = true;
            }

            static::$options[$arg] = $value;
        }
    }

    /**
     * Returns the command line string portions of the arguments, minus
     * any options, as a string. This is used to pass along to the main
     * CodeIgniter application.
     */
    public static function getURI(): string
    {
        return implode('/', static::$segments);
    }

    /**
     * Returns an individual segment.
     *
     * This ignores any options that might have been dispersed between
     * valid segments in the command:
     *
     *  // segment(3) is 'three', not '-f' or 'anOption'
     *  > php spark one two -f anOption three
     *
     * **IMPORTANT:** The index here is one-based instead of zero-based.
     *
     * @return string|null
     */
    public static function getSegment(int $index)
    {
        return static::$segments[$index - 1] ?? null;
    }

    /**
     * Returns the raw array of segments found.
     *
     * @return list<string>
     */
    public static function getSegments(): array
    {
        return static::$segments;
    }

    /**
     * Gets the value of an individual option.
     *
     * * If the option was passed without a value, this will return `true`.
     * * If the option was not passed at all, this will return `null`.
     * * If the option was an array of values, this will return the last value passed for that option.
     *
     * @return string|true|null
     */
    public static function getOption(string $name)
    {
        if (! array_key_exists($name, static::$options)) {
            return null;
        }

        $value = static::$options[$name] ?? true;

        if (! is_array($value)) {
            return $value;
        }

        return $value[count($value) - 1] ?? true;
    }

    /**
     * Gets the raw value of an individual option, which may be a string,
     * a list of `string|null`, or `true` if the option was passed without a value.
     *
     * @return list<string|null>|string|true|null
     */
    public static function getRawOption(string $name): array|string|true|null
    {
        if (! array_key_exists($name, static::$options)) {
            return null;
        }

        return static::$options[$name] ?? true;
    }

    /**
     * Returns the raw array of options found.
     *
     * @return array<string, list<string|null>|string|null>
     */
    public static function getOptions(): array
    {
        return static::$options;
    }

    /**
     * Returns the options as a string, suitable for passing along on
     * the CLI to other commands.
     *
     * @param bool $useLongOpts Use '--' for long options?
     * @param bool $trim        Trim final string output?
     */
    public static function getOptionString(bool $useLongOpts = false, bool $trim = false): string
    {
        if (static::$options === []) {
            return '';
        }

        $out = [];

        $valueCallback = static function (?string $value, string $name) use (&$out): void {
            if ($value === null) {
                $out[] = $name;
            } elseif (str_contains($value, ' ')) {
                $out[] = sprintf('%s "%s"', $name, $value);
            } else {
                $out[] = sprintf('%s %s', $name, $value);
            }
        };

        foreach (static::$options as $name => $value) {
            $name = $useLongOpts && mb_strlen($name) > 1 ? "--{$name}" : "-{$name}";

            if (is_array($value)) {
                foreach ($value as $val) {
                    $valueCallback($val, $name);
                }
            } else {
                $valueCallback($value, $name);
            }
        }

        $output = implode(' ', $out);

        return $trim ? $output : "{$output} ";
    }

    /**
     * Returns a well formatted table
     *
     * @param list<array<array-key, mixed>> $tbody List of rows
     * @param list<string>                  $thead List of columns
     *
     * @return void
     */
    public static function table(array $tbody, array $thead = [])
    {
        static::write((new Table($tbody, $thead))->render());
    }

    /**
     * Writes without touching the cursor state.
     *
     * @param resource|string $handle A stream, or the `STDOUT` placeholder string outside the CLI.
     *
     * @return void
     */
    protected static function fwrite($handle, string $string)
    {
        if ($handle === STDOUT) {
            static::getOutput()->writeRaw($string);

            return;
        }

        static::getOutput()->getInputOutput()->fwrite($handle, $string);
    }

    /**
     * Runs the callback with the output swapped for one targeting STDERR, so the callback must not swap it again.
     *
     * @param callable(): void $callback
     *
     * @internal
     */
    public static function redirectToStderr(callable $callback): void
    {
        if (! is_cli()) {
            $callback();

            return;
        }

        $previous = static::getOutput();

        static::setOutput($previous->forStderr());

        try {
            $callback();
        } finally {
            static::setOutput($previous);
        }
    }

    /**
     * Testing purpose only
     *
     * @internal
     */
    public static function reset(): void
    {
        static::$initialized = false;
        static::$segments    = [];
        static::$options     = [];

        self::resetOutput();
    }

    /**
     * Testing purpose only
     *
     * @internal
     */
    public static function resetLastWrite(): void
    {
        static::getOutput()->setCursorAtLineStart(false);
    }

    /**
     * @internal
     */
    public static function getInputOutput(): InputOutput
    {
        return static::getOutput()->getInputOutput();
    }

    /**
     * @internal
     */
    public static function setInputOutput(InputOutput $io): void
    {
        static::getOutput()->setInputOutput($io);
    }

    /**
     * @internal
     */
    public static function resetInputOutput(): void
    {
        static::setInputOutput(new InputOutput());
    }

    private static function resetOutput(): void
    {
        self::$output       = new Output();
        self::$progressBars = new WeakMap();
    }

    private static function getProgressBar(Output $output): ProgressBar
    {
        self::$progressBars ??= new WeakMap();

        self::$progressBars[$output] ??= new ProgressBar($output);

        return self::$progressBars[$output];
    }
}

// Ensure the class is initialized. Done outside of code coverage
CLI::init(); // @codeCoverageIgnore
