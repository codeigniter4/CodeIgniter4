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

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Exceptions\LogicException;
use Config\App;
use ErrorException;
use FilesystemIterator;
use Locale;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * @see \CodeIgniter\Commands\Translation\LocalizationSyncTest
 */
class LocalizationSync extends BaseCommand
{
    protected $group       = 'Translation';
    protected $name        = 'lang:sync';
    protected $description = 'Synchronize translation files from one language to another.';
    protected $usage       = 'lang:sync [options]';
    protected $arguments   = [];
    protected $options     = [
        '--locale' => 'The original locale (en, ru, etc.).',
        '--target' => 'Target locale (en, ru, etc.).',
    ];
    private string $languagePath;

    public function run(array $params)
    {
        $optionTargetLocale = '';
        $optionLocale       = $params['locale'] ?? Locale::getDefault();
        $this->languagePath = APPPATH . 'Language';

        if (isset($params['target']) && $params['target'] !== '') {
            $optionTargetLocale = $params['target'];
        }

        if (! in_array($optionLocale, config(App::class)->supportedLocales, true)) {
            CLI::error(
                'Error: "' . $optionLocale . '" is not supported. Supported locales: '
                . implode(', ', config(App::class)->supportedLocales),
            );

            return EXIT_USER_INPUT;
        }

        if ($optionTargetLocale === '') {
            CLI::error(
                'Error: "--target" is not configured. Supported locales: '
                . implode(', ', config(App::class)->supportedLocales),
            );

            return EXIT_USER_INPUT;
        }

        if (! in_array($optionTargetLocale, config(App::class)->supportedLocales, true)) {
            CLI::error(
                'Error: "' . $optionTargetLocale . '" is not supported. Supported locales: '
                . implode(', ', config(App::class)->supportedLocales),
            );

            return EXIT_USER_INPUT;
        }

        if ($optionTargetLocale === $optionLocale) {
            CLI::error(
                'Error: You cannot have the same values for "--target" and "--locale".',
            );

            return EXIT_USER_INPUT;
        }

        if (ENVIRONMENT === 'testing') {
            $this->languagePath = SUPPORTPATH . 'Language';
        }

        if ($this->process($optionLocale, $optionTargetLocale) === EXIT_ERROR) {
            return EXIT_ERROR;
        }

        CLI::write('All operations done!');

        return EXIT_SUCCESS;
    }

    private function process(string $originalLocale, string $targetLocale): int
    {
        $originalLocaleDir = $this->languagePath . DIRECTORY_SEPARATOR . $originalLocale;
        $targetLocaleDir   = $this->languagePath . DIRECTORY_SEPARATOR . $targetLocale;

        if (! is_dir($originalLocaleDir)) {
            CLI::error(
                'Error: The "' . clean_path($originalLocaleDir) . '" directory was not found.',
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
                'Error: The target directory "' . clean_path($targetLocaleDir) . '" cannot be accessed.',
            );

            return EXIT_ERROR;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $originalLocaleDir,
                FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS,
            ),
        );

        /**
         * @var array<non-empty-string, SplFileInfo> $files
         */
        $files = iterator_to_array($iterator, true);
        ksort($files);

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
                throw new LogicException('Value for the key "' . $placeholderValue . '" is of the wrong type. Only "array" or "string" is allowed.');
            }
        }

        return $mergedLanguageKeys;
    }
}
