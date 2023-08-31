<?php

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
use Locale;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class LocalizationFinder extends BaseCommand
{
    protected $group       = 'Translation';
    protected $name        = 'lang:find';
    protected $description = 'Find and save available phrases to translate';
    protected $usage       = 'lang:find [options]';
    protected $arguments   = [];
    protected $options     = [
        '--locale'   => 'Apply changes to the selected locale (en, ru, etc...).',
        '--dir'      => 'Directory for searching for translations (in relation to the APPPATH).',
        '--show-new' => 'See only new translations as table.',
        '--verbose'  => 'Output detailed information.',
    ];

    /**
     * Flag for output detailed information
     */
    private bool $verbose = false;

    private string $languagePath;

    public function run(array $params)
    {
        $this->verbose      = array_key_exists('verbose', $params);
        $cliOptionShowNew   = array_key_exists('show-new', $params);
        $cliOptionLocale    = ! empty($params['locale']) ? $params['locale'] : null;
        $cliOptionDir       = ! empty($params['dir']) ? $params['dir'] : null;
        $currentLocale      = Locale::getDefault();
        $currentDir         = APPPATH;
        $this->languagePath = $currentDir . 'Language';
        $tableRows          = [];
        $languageFoundKeys  = [];
        $countFiles         = 0;
        $countNewKeys       = 0;

        if (ENVIRONMENT === 'testing') {
            $currentDir         = SUPPORTPATH;
            $this->languagePath = $currentDir . 'Language/';
        }

        if (is_string($cliOptionLocale)) {
            if (! in_array($cliOptionLocale, config(\Config\App::class)->supportedLocales, true)) {
                CLI::error('Error: Supported locales ' . implode(', ', config(\Config\App::class)->supportedLocales));

                return -1;
            }

            $currentLocale = $cliOptionLocale;
        }

        if (is_string($cliOptionDir)) {
            $tempCurrentDir = realpath($currentDir . $cliOptionDir);

            if (false === $tempCurrentDir) {
                CLI::error('Error: Dir must be located in ' . $currentDir);

                return -1;
            }

            if (str_starts_with($tempCurrentDir, $this->languagePath)) {
                CLI::error('Error: Dir ' . $this->languagePath . ' restricted to scan.');

                return -1;
            }

            $currentDir = $tempCurrentDir;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($currentDir));
        $this->writeIsVerbose('Current locale: ' . $currentLocale);
        $this->writeIsVerbose('Find phrases in ' . $currentDir . ' folder...');

        /**
         * @var SplFileInfo $file
         */
        foreach ($iterator as $file) {
            if ($this->isIgnoredFile($file)) {
                continue;
            }

            $this->writeIsVerbose('File found: ' . mb_substr($file->getRealPath(), mb_strlen(APPPATH)));
            $countFiles++;
            $languageFoundKeys = array_replace_recursive($this->findTranslationsInFile($file), $languageFoundKeys);
        }

        ksort($languageFoundKeys);
        $languageDiff        = [];
        $languageFoundGroups = array_unique(array_keys($languageFoundKeys));

        foreach ($languageFoundGroups as $langFileName) {
            $languageStoredKeys = [];
            $languageFilePath   = $this->languagePath . DIRECTORY_SEPARATOR . $currentLocale . DIRECTORY_SEPARATOR . $langFileName . '.php';

            if (is_file($languageFilePath)) {
                /**
                 * Load old localization
                 */
                $languageStoredKeys = require $languageFilePath;
            }

            $languageDiff = $this->arrayDiffRecursive($languageFoundKeys[$langFileName], $languageStoredKeys);
            $countNewKeys += $this->arrayCountRecursive($languageDiff);

            if ($cliOptionShowNew) {
                $tableRows = array_merge($this->arrayToTableRows($langFileName, $languageDiff), $tableRows);
            } else {
                $newLanguageKeys = array_replace_recursive($languageFoundKeys[$langFileName], $languageStoredKeys);

                /**
                 * New translates exists
                 */
                if (count($languageDiff) > 0) {
                    if (false === file_put_contents($languageFilePath, $this->templateFile($newLanguageKeys))) {
                        $this->writeIsVerbose('Lang file ' . $langFileName . ' (error write).', 'red');
                    } else {
                        /**
                         * FIXME: Need help command (slow run)
                         */
                        exec('composer cs-fix --quiet ' . $this->languagePath);
                        $this->writeIsVerbose('Lang file "' . $langFileName . '" successful updated!', 'green');
                    }
                }
            }
        }

        if ($cliOptionShowNew && ! empty($tableRows)) {
            sort($tableRows);
            CLI::table($tableRows, ['File', 'Key']);
        }

        $this->writeIsVerbose('Files found: ' . $countFiles);
        $this->writeIsVerbose('New translates found: ' . $countNewKeys);
        CLI::write('All operations done!');
    }

    private function findTranslationsInFile(string|SplFileInfo $file): array
    {
        $languageFoundKeys = [];

        if (is_string($file) && is_file($file)) {
            $file = new SplFileInfo($file);
        }

        $fileContent = file_get_contents($file->getRealPath());
        preg_match_all('/lang\(\'([._a-z0-9]+)\'\)/ui', $fileContent, $matches);

        if (empty($matches[1])) {
            return [];
        }

        foreach ($matches[1] as $phraseKey) {
            $phraseKeys = explode('.', $phraseKey);

            /**
             * Language key not have Filename or Lang key
             */
            if (count($phraseKeys) < 2) {
                continue;
            }

            $languageFileName   = array_shift($phraseKeys);
            $isEmptyNestedArray = (! empty($languageFileName) && empty($phraseKeys[0]))
                || (empty($languageFileName) && ! empty($phraseKeys[0]))
                || (empty($languageFileName) && empty($phraseKeys[0]));

            if ($isEmptyNestedArray) {
                continue;
            }

            /**
             * Language key as string
             */
            if (count($phraseKeys) === 1) {
                /**
                 * Add found language keys to temporary array
                 */
                $languageFoundKeys[$languageFileName][$phraseKeys[0]] = $phraseKey;
            } else {
                $childKeys                            = $this->buildMultiArray($phraseKeys, $phraseKey);
                $languageFoundKeys[$languageFileName] = array_replace_recursive($languageFoundKeys[$languageFileName] ?? [], $childKeys);
            }
        }

        return $languageFoundKeys;
    }

    private function isIgnoredFile(SplFileInfo $file): bool
    {
        if ($file->isDir() || str_contains($file->getRealPath(), $this->languagePath)) {
            return true;
        }

        return (bool) ('php' !== $file->getExtension());
    }

    private function templateFile(array $language = []): string
    {
        if (! empty($language)) {
            $languageArrayString = $this->languageKeysDump($language);

            return <<<PHP
                <?php

                return {$languageArrayString};
                PHP;
        }

        return <<<'PHP'
            <?php

            return [];
            PHP;
    }

    /**
     * Create multidimensional array from another keys
     */
    private function buildMultiArray(array $fromKeys, string $lastArrayValue = ''): array
    {
        $newArray  = [];
        $lastIndex = array_pop($fromKeys);
        $current   = &$newArray;

        foreach ($fromKeys as $value) {
            $current[$value] = [];
            $current         = &$current[$value];
        }

        $current[$lastIndex] = $lastArrayValue;

        return $newArray;
    }

    /**
     * Compare recursive two arrays and return new array (diference)
     */
    private function arrayDiffRecursive(array $originalArray, array $compareArray): array
    {
        $difference = [];

        if (count($compareArray) < 1) {
            return $originalArray;
        }

        foreach ($originalArray as $originalKey => $originalValue) {
            if (is_array($originalValue)) {
                $diffArrays = null;

                if (isset($compareArray[$originalKey])) {
                    $diffArrays = $this->arrayDiffRecursive($originalValue, $compareArray[$originalKey]);
                } else {
                    $difference[$originalKey] = $originalValue;
                }

                if (! empty($diffArrays)) {
                    $difference[$originalKey] = $diffArrays;
                }
            } elseif (is_string($originalValue) && ! array_key_exists($originalKey, $compareArray)) {
                $difference[$originalKey] = $originalValue;
            }
        }

        return $difference;
    }

    /**
     * Convert multi arrays to specific CLI table rows (flat array)
     */
    private function arrayToTableRows(string $langFileName, array $array): array
    {
        $rows = [];

        foreach ($array as $value) {
            if (is_array($value)) {
                $rows = array_merge($rows, $this->arrayToTableRows($langFileName, $value));

                continue;
            }

            if (is_string($value)) {
                $rows[] = [$langFileName, $value];
            }
        }

        return $rows;
    }

    private function arrayCountRecursive(array $array, int $counter = 0): int
    {
        foreach ($array as $value) {
            if (! is_array($value)) {
                $counter++;
            } else {
                $counter = $this->arrayCountRecursive($value, $counter);
            }
        }

        return $counter;
    }

    private function languageKeysDump(array $inputArray): string
    {
        return var_export($inputArray, true);
    }

    /**
     * Show details in the console if the flag is set
     */
    private function writeIsVerbose(string $text = '', ?string $foreground = null, ?string $background = null): void
    {
        if ($this->verbose) {
            CLI::write($text, $foreground, $background);
        }
    }
}
