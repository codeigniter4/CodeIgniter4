<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Language;

use Config\Services;
use InvalidArgumentException;
use MessageFormatter;

/**
 * Handle system messages and localization.
 *
 * Locale-based, built on top of PHP internationalization.
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
     * @var array
     */
    protected $language = [];

    /**
     * The current language/locale to work with.
     *
     * @var string
     */
    protected $locale;

    /**
     * Boolean value whether the intl
     * libraries exist on the system.
     *
     * @var bool
     */
    protected $intlSupport = false;

    /**
     * Stores filenames that have been
     * loaded so that we don't load them again.
     *
     * @var array
     */
    protected $loadedFiles = [];

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
     * @return list<string>|string
     */
    public function getLine(string $line, array $args = [])
    {
        // if no file is given, just parse the line
        if (strpos($line, '.') === false) {
            return $this->formatMessage($line, $args);
        }

        // Parse out the file name and the actual alias.
        // Will load the language file and strings.
        [$file, $parsedLine] = $this->parseLine($line, $this->locale);

        $output = $this->getTranslationOutput($this->locale, $file, $parsedLine);

        if ($output === null && strpos($this->locale, '-')) {
            [$locale] = explode('-', $this->locale, 2);

            [$file, $parsedLine] = $this->parseLine($line, $locale);

            $output = $this->getTranslationOutput($locale, $file, $parsedLine);
        }

        // if still not found, try English
        if ($output === null) {
            [$file, $parsedLine] = $this->parseLine($line, 'en');

            $output = $this->getTranslationOutput('en', $file, $parsedLine);
        }

        $output ??= $line;

        return $this->formatMessage($output, $args);
    }

    /**
     * @return array|string|null
     */
    protected function getTranslationOutput(string $locale, string $file, string $parsedLine)
    {
        $output = $this->language[$locale][$file][$parsedLine] ?? null;
        if ($output !== null) {
            return $output;
        }

        foreach (explode('.', $parsedLine) as $row) {
            if (! isset($current)) {
                $current = $this->language[$locale][$file] ?? null;
            }

            $output = $current[$row] ?? null;
            if (is_array($output)) {
                $current = $output;
            }
        }

        if ($output !== null) {
            return $output;
        }

        $row = current(explode('.', $parsedLine));
        $key = substr($parsedLine, strlen($row) + 1);

        return $this->language[$locale][$file][$row][$key] ?? null;
    }

    /**
     * Parses the language string which should include the
     * filename as the first segment (separated by period).
     */
    protected function parseLine(string $line, string $locale): array
    {
        $file = substr($line, 0, strpos($line, '.'));
        $line = substr($line, strlen($file) + 1);

        if (! isset($this->language[$locale][$file]) || ! array_key_exists($line, $this->language[$locale][$file])) {
            $this->load($file, $locale);
        }

        return [$file, $line];
    }

    /**
     * Advanced message formatting.
     *
     * @param array|string $message
     * @param string[]     $args
     *
     * @return array|string
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
            throw new InvalidArgumentException(
                lang('Language.invalidMessageFormat', [$message, implode(',', $args)])
            );
        }

        return $formatted;
    }

    /**
     * Loads a language file in the current locale. If $return is true,
     * will return the file's contents, otherwise will merge with
     * the existing language lines.
     *
     * @return array|void
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
    }

    /**
     * A simple method for including files that can be
     * overridden during testing.
     */
    protected function requireFile(string $path): array
    {
        $files   = Services::locator()->search($path, 'php', false);
        $strings = [];

        foreach ($files as $file) {
            // On some OS's we were seeing failures
            // on this command returning boolean instead
            // of array during testing, so we've removed
            // the require_once for now.
            if (is_file($file)) {
                $strings[] = require $file;
            }
        }

        if (isset($strings[1])) {
            $string = array_shift($strings);

            $strings = array_replace_recursive($string, ...$strings);
        } elseif (isset($strings[0])) {
            $strings = $strings[0];
        }

        return $strings;
    }
}
