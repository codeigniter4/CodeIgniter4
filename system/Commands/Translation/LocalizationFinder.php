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
use CodeIgniter\Helpers\Array\ArrayHelper;
use Config\App;
use Locale;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Finds and saves available phrases to translate.
 */
#[Command(
    name: 'lang:find',
    description: 'Find and save available phrases to translate.',
    group: 'Translation',
)]
class LocalizationFinder extends AbstractCommand
{
    private string $languagePath;

    protected function configure(): void
    {
        $this
            ->addOption(new Option(
                name: 'locale',
                description: 'Specify locale (en, ru, etc.) to save files.',
                requiresValue: true,
                default: '',
            ))
            ->addOption(new Option(
                name: 'dir',
                description: 'Directory to search for translations relative to APPPATH.',
                requiresValue: true,
                default: '',
            ))
            ->addOption(new Option(
                name: 'show-new',
                description: 'Show only new translations in table. Does not write to files.',
            ))
            ->addOption(new Option(
                name: 'verbose',
                description: 'Output detailed information.',
            ));
    }

    protected function execute(array $arguments, array $options): int
    {
        $locale = $options['locale'];
        assert(is_string($locale));

        $dir = $options['dir'];
        assert(is_string($dir));

        $currentLocale = Locale::getDefault();

        ['currentDir' => $currentDir, 'languagePath' => $this->languagePath] = $this->resolvePaths();

        if ($locale !== '') {
            $supportedLocales = config(App::class)->supportedLocales;

            if (! in_array($locale, $supportedLocales, true)) {
                CLI::error(
                    sprintf(
                        'Error: "%s" is not supported. Supported locales: %s',
                        $locale,
                        implode(', ', $supportedLocales),
                    ),
                    'light_gray',
                    'red',
                );

                return EXIT_USER_INPUT;
            }

            $currentLocale = $locale;
        }

        if ($dir !== '') {
            $tempCurrentDir = realpath($currentDir . $dir);

            if ($tempCurrentDir === false) {
                CLI::error(sprintf('Error: Directory must be located in "%s"', $currentDir), 'light_gray', 'red');

                return EXIT_USER_INPUT;
            }

            if ($this->isSubdirectory($tempCurrentDir, $this->languagePath)) {
                CLI::error(sprintf('Error: Directory "%s" restricted to scan.', $this->languagePath), 'light_gray', 'red');

                return EXIT_USER_INPUT;
            }

            $currentDir = $tempCurrentDir;
        }

        $this->process($currentDir, $currentLocale);

        CLI::write('All operations done!', 'green');

        return EXIT_SUCCESS;
    }

    /**
     * Resolves the directory to scan and the directory that holds the language
     * files, swapping in the test fixtures under the testing environment.
     *
     * @return array{currentDir: string, languagePath: string}
     */
    private function resolvePaths(): array
    {
        if (service('environment')->isTesting()) {
            return [
                'currentDir'   => SUPPORTPATH . 'Services' . DIRECTORY_SEPARATOR,
                'languagePath' => SUPPORTPATH . 'Language',
            ];
        }

        return [
            'currentDir'   => APPPATH,
            'languagePath' => APPPATH . 'Language',
        ];
    }

    private function process(string $currentDir, string $currentLocale): void
    {
        $showNew = $this->getValidatedOption('show-new') === true;
        $verbose = $this->getValidatedOption('verbose') === true;

        $tableRows    = [];
        $countNewKeys = 0;

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($currentDir));
        $files    = iterator_to_array($iterator);
        ksort($files);

        [
            'foundLanguageKeys' => $foundLanguageKeys,
            'badLanguageKeys'   => $badLanguageKeys,
            'countFiles'        => $countFiles,
        ] = $this->findLanguageKeysInFiles($files);

        ksort($foundLanguageKeys);

        foreach ($foundLanguageKeys as $langFileName => $foundKeys) {
            $languageStoredKeys = [];
            $languageFilePath   = $this->languagePath . DIRECTORY_SEPARATOR . $currentLocale . DIRECTORY_SEPARATOR . $langFileName . '.php';

            if (is_file($languageFilePath)) {
                // Load old localization
                $languageStoredKeys = require $languageFilePath;
            }

            // Keys already resolvable from any namespace's language file (framework, packages, or app)
            // are not new and must not be re-reported or written.
            $resolvedKeys = $this->findResolvedTranslations($langFileName, $currentLocale);

            $languageDiff = ArrayHelper::recursiveDiff($foundKeys, $resolvedKeys);
            $countNewKeys += ArrayHelper::recursiveCount($languageDiff);

            if ($showNew) {
                $tableRows = array_merge($this->arrayToTableRows($langFileName, $languageDiff), $tableRows);
            } else {
                $newLanguageKeys = array_replace_recursive($languageDiff, $languageStoredKeys);

                if ($languageDiff !== []) {
                    if (file_put_contents($languageFilePath, $this->templateFile($newLanguageKeys)) === false) {
                        $this->writeIsVerbose(sprintf('Lang file %s (error write).', $langFileName), 'red');
                    } else {
                        $this->writeIsVerbose(sprintf('Lang file "%s" successful updated!', $langFileName), 'green');
                    }
                }
            }
        }

        if ($showNew && $tableRows !== []) {
            sort($tableRows);
            CLI::table($tableRows, ['File', 'Key']);
        }

        if (! $showNew && $countNewKeys > 0) {
            CLI::write('Note: You need to run your linting tool to fix coding standards issues.', 'white', 'red');
        }

        $this->writeIsVerbose(sprintf('Files found: %d', $countFiles));
        $this->writeIsVerbose(sprintf('New translates found: %d', $countNewKeys));
        $this->writeIsVerbose(sprintf('Bad translates found: %d', count($badLanguageKeys)));

        if ($verbose && $badLanguageKeys !== []) {
            $tableBadRows = [];

            foreach ($badLanguageKeys as $value) {
                $tableBadRows[] = [$value[1], $value[0]];
            }

            ArrayHelper::sortValuesByNatural($tableBadRows, 0);

            CLI::table($tableBadRows, ['Bad Key', 'Filepath']);
        }
    }

    /**
     * Loads the translations already resolvable for the given file and locale
     * from every registered namespace (framework, packages, and app).
     *
     * @return array<string, mixed>
     */
    private function findResolvedTranslations(string $langFileName, string $currentLocale): array
    {
        $translations = [];

        foreach (service('locator')->search("Language/{$currentLocale}/{$langFileName}.php", 'php', false) as $file) {
            if (! is_file($file)) {
                continue;
            }

            $keys = require $file;

            if (is_array($keys)) {
                $translations[] = $keys;
            }
        }

        if ($translations === []) {
            return [];
        }

        return array_replace_recursive(...$translations);
    }

    /**
     * @return array{foundLanguageKeys: array<string, mixed>, badLanguageKeys: list<array{string, string}>}
     */
    private function findTranslationsInFile(SplFileInfo $file): array
    {
        $foundLanguageKeys = [];
        $badLanguageKeys   = [];

        $fileContent = file_get_contents($file->getRealPath());
        preg_match_all('/lang\(\'([._a-z0-9\-]+)\'\)/ui', $fileContent, $matches);

        if ($matches[1] === []) {
            return compact('foundLanguageKeys', 'badLanguageKeys');
        }

        foreach ($matches[1] as $phraseKey) {
            $phraseKeys = explode('.', $phraseKey);

            // Language key not have Filename or Lang key
            if (count($phraseKeys) < 2) {
                $badLanguageKeys[] = [mb_substr($file->getRealPath(), mb_strlen(ROOTPATH)), $phraseKey];

                continue;
            }

            $languageFileName   = array_shift($phraseKeys);
            $isEmptyNestedArray = ($languageFileName !== '' && $phraseKeys[0] === '')
                || ($languageFileName === '' && $phraseKeys[0] !== '')
                || ($languageFileName === '' && $phraseKeys[0] === '');

            if ($isEmptyNestedArray) {
                $badLanguageKeys[] = [mb_substr($file->getRealPath(), mb_strlen(ROOTPATH)), $phraseKey];

                continue;
            }

            if (count($phraseKeys) === 1) {
                $foundLanguageKeys[$languageFileName][$phraseKeys[0]] = $phraseKey;
            } else {
                $childKeys = $this->buildMultiArray($phraseKeys, $phraseKey);

                $foundLanguageKeys[$languageFileName] = array_replace_recursive($foundLanguageKeys[$languageFileName] ?? [], $childKeys);
            }
        }

        return compact('foundLanguageKeys', 'badLanguageKeys');
    }

    private function isIgnoredFile(SplFileInfo $file): bool
    {
        if ($file->isDir() || $this->isSubdirectory($file->getRealPath(), $this->languagePath)) {
            return true;
        }

        return $file->getExtension() !== 'php';
    }

    /**
     * @param array<array-key, mixed> $language
     */
    private function templateFile(array $language = []): string
    {
        if ($language !== []) {
            $languageArrayString = var_export($language, true);

            $code = <<<PHP
                <?php

                return {$languageArrayString};

                PHP;

            return $this->replaceArraySyntax($code);
        }

        return <<<'PHP'
            <?php

            return [];

            PHP;
    }

    private function replaceArraySyntax(string $code): string
    {
        $tokens    = token_get_all($code);
        $newTokens = $tokens;

        foreach ($tokens as $i => $token) {
            if (is_array($token)) {
                [$tokenId, $tokenValue] = $token;

                // Replace "array ("
                if (
                    $tokenId === T_ARRAY
                    && $tokens[$i + 1][0] === T_WHITESPACE
                    && $tokens[$i + 2] === '('
                ) {
                    $newTokens[$i][1]     = '[';
                    $newTokens[$i + 1][1] = '';
                    $newTokens[$i + 2]    = '';
                }

                // Replace indent
                if ($tokenId === T_WHITESPACE && preg_match('/\n([ ]+)/u', $tokenValue, $matches)) {
                    $newTokens[$i][1] = "\n{$matches[1]}{$matches[1]}";
                }
            } // Replace ")"
            elseif ($token === ')') {
                $newTokens[$i] = ']';
            }
        }

        $output = '';

        foreach ($newTokens as $token) {
            $output .= $token[1] ?? $token;
        }

        return $output;
    }

    /**
     * Create multidimensional array from another keys
     *
     * @param list<string> $fromKeys
     *
     * @return array<string, mixed>
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
     * Convert multi arrays to specific CLI table rows (flat array)
     *
     * @param array<array-key, mixed> $array
     *
     * @return list<array{string, string}>
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

    /**
     * Show details in the console if the flag is set
     */
    private function writeIsVerbose(string $text = '', ?string $foreground = null, ?string $background = null): void
    {
        if ($this->getValidatedOption('verbose') === true) {
            CLI::write($text, $foreground, $background);
        }
    }

    private function isSubdirectory(string $directory, string $rootDirectory): bool
    {
        return 0 === strncmp($directory, $rootDirectory, strlen($directory));
    }

    /**
     * @param list<SplFileInfo> $files
     *
     * @return array{'foundLanguageKeys': array<string, array<string, string>>, 'badLanguageKeys': array<int, array<int, string>>, 'countFiles': int}
     */
    private function findLanguageKeysInFiles(array $files): array
    {
        $foundLanguageKeys = [];
        $badLanguageKeys   = [];
        $countFiles        = 0;

        foreach ($files as $file) {
            if ($this->isIgnoredFile($file)) {
                continue;
            }

            $this->writeIsVerbose(sprintf('File found: %s', mb_substr($file->getRealPath(), mb_strlen(APPPATH))));
            $countFiles++;

            $findInFile = $this->findTranslationsInFile($file);

            $foundLanguageKeys = array_replace_recursive($findInFile['foundLanguageKeys'], $foundLanguageKeys);
            $badLanguageKeys   = array_merge($findInFile['badLanguageKeys'], $badLanguageKeys);
        }

        return compact('foundLanguageKeys', 'badLanguageKeys', 'countFiles');
    }
}
