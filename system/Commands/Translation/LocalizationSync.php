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

namespace CodeIgniter\Commands\Translation;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Input\Option;
use CodeIgniter\Exceptions\LogicException;
use Config\App;
use ErrorException;
use FilesystemIterator;
use Locale;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Synchronizes translation files from one language to another.
 */
#[Command(
    name: 'lang:sync',
    description: 'Synchronize translation files from one language to another.',
    group: 'Translation',
)]
class LocalizationSync extends AbstractCommand
{
    private string $languagePath;

    protected function configure(): void
    {
        $this
            ->addOption(new Option(
                name: 'locale',
                description: 'The original locale (en, ru, etc.).',
                requiresValue: true,
                default: '',
            ))
            ->addOption(new Option(
                name: 'target',
                description: 'Target locale (en, ru, etc.).',
                requiresValue: true,
                default: '',
            ));
    }

    protected function execute(array $arguments, array $options): int
    {
        $locale = $options['locale'];
        assert(is_string($locale));

        $target = $options['target'];
        assert(is_string($target));

        $this->languagePath = APPPATH . 'Language';
        $supportedLocales   = config(App::class)->supportedLocales;

        if ($locale === '') {
            $locale = Locale::getDefault();
        }

        if (! in_array($locale, $supportedLocales, true)) {
            return $this->errorUnsupportedLocale($locale);
        }

        if ($target === '') {
            CLI::error(
                sprintf(
                    'Error: "--target" is not configured. Supported locales: %s',
                    implode(', ', $supportedLocales),
                ),
                'light_gray',
                'red',
            );

            return EXIT_USER_INPUT;
        }

        if (! in_array($target, $supportedLocales, true)) {
            return $this->errorUnsupportedLocale($target);
        }

        if ($target === $locale) {
            CLI::error('Error: You cannot have the same values for "--target" and "--locale".', 'light_gray', 'red');

            return EXIT_USER_INPUT;
        }

        if (service('environment')->isTesting()) {
            $this->languagePath = SUPPORTPATH . 'Language';
        }

        if ($this->process($locale, $target) === EXIT_ERROR) {
            return EXIT_ERROR;
        }

        CLI::write('All operations done!', 'green');

        return EXIT_SUCCESS;
    }

    /**
     * Writes the unsupported-locale error and returns the user-input exit code.
     */
    private function errorUnsupportedLocale(string $locale): int
    {
        CLI::error(
            sprintf(
                'Error: "%s" is not supported. Supported locales: %s',
                $locale,
                implode(', ', config(App::class)->supportedLocales),
            ),
            'light_gray',
            'red',
        );

        return EXIT_USER_INPUT;
    }

    private function process(string $originalLocale, string $targetLocale): int
    {
        $originalLocaleDir = $this->languagePath . DIRECTORY_SEPARATOR . $originalLocale;
        $targetLocaleDir   = $this->languagePath . DIRECTORY_SEPARATOR . $targetLocale;

        if (! is_dir($originalLocaleDir)) {
            CLI::error(
                sprintf('Error: The "%s" directory was not found.', clean_path($originalLocaleDir)),
                'light_gray',
                'red',
            );

            return EXIT_ERROR;
        }

        // Unifying the error - mkdir() may cause an exception.
        try {
            if (! is_dir($targetLocaleDir) && ! mkdir($targetLocaleDir, 0775)) {
                throw new ErrorException();
            }
        } catch (ErrorException $e) {
            CLI::error(
                sprintf('Error: The target directory "%s" cannot be accessed.', clean_path($targetLocaleDir)),
                'light_gray',
                'red',
            );

            return EXIT_ERROR;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $originalLocaleDir,
                FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS,
            ),
        );

        $files = iterator_to_array($iterator);
        ksort($files);

        /** @var SplFileInfo $originalLanguageFile */
        foreach ($files as $originalLanguageFile) {
            if ($originalLanguageFile->getExtension() !== 'php') {
                continue;
            }

            $targetLanguageFile = $targetLocaleDir . DIRECTORY_SEPARATOR . $originalLanguageFile->getFilename();

            $targetLanguageKeys   = [];
            $originalLanguageKeys = include $originalLanguageFile;

            if (is_file($targetLanguageFile)) {
                $targetLanguageKeys = include $targetLanguageFile;
            }

            $targetLanguageKeys = $this->mergeLanguageKeys($originalLanguageKeys, $targetLanguageKeys, $originalLanguageFile->getBasename('.php'));

            $content = "<?php\n\nreturn " . var_export($targetLanguageKeys, true) . ";\n";
            file_put_contents($targetLanguageFile, $content);
        }

        return EXIT_SUCCESS;
    }

    /**
     * @param array<string, array<string,mixed>|string|null> $originalLanguageKeys
     * @param array<string, array<string,mixed>|string|null> $targetLanguageKeys
     *
     * @return array<string, array<string,mixed>|string|null>
     */
    private function mergeLanguageKeys(array $originalLanguageKeys, array $targetLanguageKeys, string $prefix = ''): array
    {
        $mergedLanguageKeys = [];

        foreach ($originalLanguageKeys as $key => $value) {
            $placeholderValue = $prefix !== '' ? $prefix . '.' . $key : $key;

            if (is_string($value)) {
                // Keep the old value
                // TODO: The value type may not match the original one
                if (array_key_exists($key, $targetLanguageKeys)) {
                    $mergedLanguageKeys[$key] = $targetLanguageKeys[$key];

                    continue;
                }

                // Set new key with placeholder
                $mergedLanguageKeys[$key] = $placeholderValue;
            } elseif (is_array($value)) {
                if (! array_key_exists($key, $targetLanguageKeys)) {
                    $mergedLanguageKeys[$key] = $this->mergeLanguageKeys($value, [], $placeholderValue);

                    continue;
                }

                $mergedLanguageKeys[$key] = $this->mergeLanguageKeys($value, $targetLanguageKeys[$key], $placeholderValue);
            } else {
                throw new LogicException(sprintf(
                    'Value for the key "%s" is of the wrong type. Only "array" or "string" is allowed.',
                    $placeholderValue,
                ));
            }
        }

        return $mergedLanguageKeys;
    }
}
