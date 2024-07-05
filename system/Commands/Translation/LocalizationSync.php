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
use CodeIgniter\Helpers\Array\ArrayHelper;
use Config\App;
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
                . implode(', ', config(App::class)->supportedLocales)
            );

            return EXIT_USER_INPUT;
        }

        if ($optionTargetLocale === '') {
            CLI::error(
                'Error: "--target" is not configured. Supported locales: '
                . implode(', ', config(App::class)->supportedLocales)
            );

            return EXIT_USER_INPUT;
        }

        if (! in_array($optionTargetLocale, config(App::class)->supportedLocales, true)) {
            CLI::error(
                'Error: "' . $optionTargetLocale . '" is not supported. Supported locales: '
                . implode(', ', config(App::class)->supportedLocales)
            );

            return EXIT_USER_INPUT;
        }

        if ($optionTargetLocale === $optionLocale) {
            CLI::error(
                'Error: You cannot have the same values "--target" and "--locale".'
            );

            return EXIT_USER_INPUT;
        }

        if (ENVIRONMENT === 'testing') {
            $this->languagePath = SUPPORTPATH . 'Language';
        }

        $this->process($optionLocale, $optionTargetLocale);

        CLI::write('All operations done!');

        return EXIT_SUCCESS;
    }

    private function process(string $originalLocale, string $targetLocale): void
    {
        $originalLocaleDir = $this->languagePath . DIRECTORY_SEPARATOR . $originalLocale;
        $targetLocaleDir   = $this->languagePath . DIRECTORY_SEPARATOR . $targetLocale;

        if (! is_dir($originalLocaleDir)) {
            CLI::error(
                'Error: The "' . $originalLocaleDir . '" directory was not found.'
            );
        }

        if (! is_dir($targetLocaleDir) && ! mkdir($targetLocaleDir, 0775)) {
            CLI::error(
                'Error: The target directory "' . $targetLocaleDir . '" cannot be accessed.'
            );
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($originalLocaleDir));

        /**
         * @var list<SplFileInfo> $files
         */
        $files = iterator_to_array($iterator, true);
        ksort($files);

        foreach ($files as $originalLanguageFile) {
            if ($this->isIgnoredFile($originalLanguageFile)) {
                continue;
            }

            $targetLanguageFile = $targetLocaleDir . DIRECTORY_SEPARATOR . $originalLanguageFile->getFilename();

            $targetLanguageKeys   = [];
            $originalLanguageKeys = include $originalLanguageFile;

            if (is_file($targetLanguageFile)) {
                $targetLanguageKeys = include $targetLanguageFile;
            }

            $targetLanguageKeys = $this->mergeLanguageKeys($originalLanguageKeys, $targetLanguageKeys, $originalLanguageFile->getBasename('.php'));
            ksort($targetLanguageKeys);

            $content = "<?php\n\nreturn " . var_export($targetLanguageKeys, true) . ";\n";
            file_put_contents($targetLanguageFile, $content);
        }
    }

    /**
     * @param array<string, array<string,mixed>|string|null> $originalLanguageKeys
     * @param array<string, array<string,mixed>|string|null> $targetLanguageKeys
     *
     * @return array<string, array<string,mixed>|string|null>
     */
    private function mergeLanguageKeys(array $originalLanguageKeys, array $targetLanguageKeys, string $prefix = ''): array
    {
        foreach ($originalLanguageKeys as $key => $value) {
            $placeholderValue = $prefix === '' ? $prefix . '.' . $key : $key;

            if (! is_array($value)) {
                // Keep the old value
                // TODO: The value type may not match the original one
                if (array_key_exists($key, $targetLanguageKeys)) {
                    continue;
                }

                // Set new key with placeholder
                $targetLanguageKeys[$key] = $placeholderValue;
            } else {
                if (! array_key_exists($key, $targetLanguageKeys)) {
                    $targetLanguageKeys[$key] = [];
                }

                $targetLanguageKeys[$key] = $this->mergeLanguageKeys($value, $targetLanguageKeys[$key], $placeholderValue);
            }
        }

        return ArrayHelper::intersectKeyRecursive($targetLanguageKeys, $originalLanguageKeys);
    }

    private function isIgnoredFile(SplFileInfo $file): bool
    {
        return $file->isDir() || $file->getFilename() === '.' || $file->getFilename() === '..' || $file->getExtension() !== 'php';
    }
}
