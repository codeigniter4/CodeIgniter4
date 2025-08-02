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

namespace CodeIgniter\Language;

use IntlException;
use MessageFormatter;

/**
 * Handle system messages and localization.
 *
 * Locale-based, built on top of PHP internationalization.
 *
 * @phpstan-type LoadedStrings array<string, array<string, array<string, string>|string>|string|list<string>>
 *
 * @see \CodeIgniter\Language\LanguageTest
 */
class Language
{
    /**
     * Stores the retrieved language lines
     * from files for faster retrieval on
     * second use.
     *
     * @var array<non-empty-string, array<non-empty-string, LoadedStrings>>
     */
    protected $language = [];

    /**
     * The current locale to work with.
     *
     * @var non-empty-string
     */
    protected $locale;

    /**
     * Boolean value whether the `intl` extension exists on the system.
     *
     * @var bool
     */
    protected $intlSupport = false;

    /**
     * Stores filenames that have been
     * loaded so that we don't load them again.
     *
     * @var array<non-empty-string, list<non-empty-string>>
     */
    protected $loadedFiles = [];

    /**
     * @param non-empty-string $locale
     */
    public function __construct(string $locale)
    {
        $this->locale = $locale;

        if (class_exists(MessageFormatter::class)) {
            $this->intlSupport = true;
        }
    }

    /**
     * Sets the current locale to use when performing string lookups.
     *
     * @param non-empty-string|null $locale
     *
     * @return $this
     */
    public function setLocale(?string $locale = null)
    {
        if ($locale !== null) {
            $this->locale = $locale;
        }

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Parses the language string for a file, loads the file, if necessary,
     * getting the line.
     *
     * @param array<array-key, float|int|string> $args
     *
     * @return list<string>|string
     */
    public function getLine(string $line, array $args = [])
    {
        // 1. Format the line as-is if it does not have a file.
        if (! str_contains($line, '.')) {
            return $this->formatMessage($line, $args);
        }

        // 2. Get the formatted line using the file and line extracted from $line and the current locale.
        [$file, $parsedLine] = $this->parseLine($line, $this->locale);

        $output = $this->getTranslationOutput($this->locale, $file, $parsedLine);

        // 3. If not found, try the locale without region (e.g., 'en-US' -> 'en').
        if ($output === null && str_contains($this->locale, '-')) {
            [$locale] = explode('-', $this->locale, 2);

            [$file, $parsedLine] = $this->parseLine($line, $locale);

            $output = $this->getTranslationOutput($locale, $file, $parsedLine);
        }

        // 4. If still not found, try English.
        if ($output === null) {
            [$file, $parsedLine] = $this->parseLine($line, 'en');

            $output = $this->getTranslationOutput('en', $file, $parsedLine);
        }

        // 5. Fallback to the original line if no translation was found.
        $output ??= $line;

        return $this->formatMessage($output, $args);
    }

    /**
     * @return list<string>|string|null
     */
    protected function getTranslationOutput(string $locale, string $file, string $parsedLine)
    {
        $output = $this->language[$locale][$file][$parsedLine] ?? null;

        if ($output !== null) {
            return $output;
        }

        // Fallback: try to traverse dot notation
        $current = $this->language[$locale][$file] ?? null;

        if (is_array($current)) {
            foreach (explode('.', $parsedLine) as $segment) {
                $output = $current[$segment] ?? null;

                if ($output === null) {
                    break;
                }

                if (is_array($output)) {
                    $current = $output;
                }
            }

            if ($output !== null && ! is_array($output)) {
                return $output;
            }
        }

        // Final fallback: try two-level access manually
        [$first, $rest] = explode('.', $parsedLine, 2) + ['', ''];

        return $this->language[$locale][$file][$first][$rest] ?? null;
    }

    /**
     * Parses the language string which should include the
     * filename as the first segment (separated by period).
     *
     * @return array{non-empty-string, non-empty-string}
     */
    protected function parseLine(string $line, string $locale): array
    {
        [$file, $line] = explode('.', $line, 2);

        if (! isset($this->language[$locale][$file]) || ! array_key_exists($line, $this->language[$locale][$file])) {
            $this->load($file, $locale);
        }

        return [$file, $line];
    }

    /**
     * Advanced message formatting.
     *
     * @param list<string>|string                $message
     * @param array<array-key, float|int|string> $args
     *
     * @return ($message is list<string> ? list<string> : string)
     */
    protected function formatMessage($message, array $args = [])
    {
        if (! $this->intlSupport || $args === []) {
            return $message;
        }

        if (is_array($message)) {
            foreach ($message as $index => $value) {
                $message[$index] = $this->formatMessage($value, $args);
            }

            return $message;
        }

        $formatted = MessageFormatter::formatMessage($this->locale, $message, $args);

        if ($formatted === false) {
            // Format again to get the error message.
            try {
                $formatter = new MessageFormatter($this->locale, $message);
                $formatted = $formatter->format($args);
                $fmtError  = sprintf('"%s" (%d)', $formatter->getErrorMessage(), $formatter->getErrorCode());
            } catch (IntlException $e) {
                $fmtError = sprintf('"%s" (%d)', $e->getMessage(), $e->getCode());
            }

            $argsAsString   = sprintf('"%s"', implode('", "', $args));
            $urlEncodedArgs = sprintf('"%s"', implode('", "', array_map(rawurlencode(...), $args)));

            log_message('error', sprintf(
                'Invalid message format: $message: "%s", $args: %s (urlencoded: %s), MessageFormatter Error: %s',
                $message,
                $argsAsString,
                $urlEncodedArgs,
                $fmtError,
            ));

            return $message . "\n【Warning】Also, invalid string(s) was passed to the Language class. See log file for details.";
        }

        return $formatted;
    }

    /**
     * Loads a language file in the current locale. If $return is true,
     * will return the file's contents, otherwise will merge with
     * the existing language lines.
     *
     * @return ($return is true ? LoadedStrings : null)
     */
    protected function load(string $file, string $locale, bool $return = false)
    {
        if (! array_key_exists($locale, $this->loadedFiles)) {
            $this->loadedFiles[$locale] = [];
        }

        if (in_array($file, $this->loadedFiles[$locale], true)) {
            // Don't load it more than once.
            return [];
        }

        if (! array_key_exists($locale, $this->language)) {
            $this->language[$locale] = [];
        }

        if (! array_key_exists($file, $this->language[$locale])) {
            $this->language[$locale][$file] = [];
        }

        $path = "Language/{$locale}/{$file}.php";

        $lang = $this->requireFile($path);

        if ($return) {
            return $lang;
        }

        $this->loadedFiles[$locale][] = $file;

        // Merge our string
        $this->language[$locale][$file] = $lang;

        return null;
    }

    /**
     * A simple method for including files that can be overridden during testing.
     *
     * @return LoadedStrings
     */
    protected function requireFile(string $path): array
    {
        $files   = service('locator')->search($path, 'php', false);
        $strings = [];

        foreach ($files as $file) {
            if (is_file($file)) {
                // On some OS, we were seeing failures on this command returning boolean instead
                // of array during testing, so we've removed the require_once for now.
                $loadedStrings = require $file;

                if (is_array($loadedStrings)) {
                    /** @var LoadedStrings $loadedStrings */
                    $strings[] = $loadedStrings;
                }
            }
        }

        $count = count($strings);

        if ($count > 1) {
            $base = array_shift($strings);

            $strings = array_replace_recursive($base, ...$strings);
        } elseif ($count === 1) {
            $strings = $strings[0];
        }

        return $strings;
    }
}
