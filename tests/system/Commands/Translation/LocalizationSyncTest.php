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

use CodeIgniter\Exceptions\LogicException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ReflectionHelper;
use CodeIgniter\Test\StreamFilterTrait;
use Config\App;
use Locale;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class LocalizationSyncTest extends CIUnitTestCase
{
    use StreamFilterTrait;
    use ReflectionHelper;

    private static string $locale;
    private static string $languageTestPath;

    /**
     * @var array<string, array<string,mixed>|string|null>
     */
    private array $expectedKeys = [
        'title'  => 'Sync.title',
        'status' => [
            'error'    => 'Sync.status.error',
            'done'     => 'Sync.status.done',
            'critical' => 'Sync.status.critical',
        ],
        'description' => 'Sync.description',
        'empty_array' => [],
        'more'        => [
            'nested' => [
                'key' => 'Sync.more.nested.key',
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        config(App::class)->supportedLocales = ['en', 'ru', 'de'];

        self::$locale           = Locale::getDefault();
        self::$languageTestPath = SUPPORTPATH . 'Language/';
        $this->makeLanguageFiles();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->clearGeneratedFiles();
    }

    public function testSyncDefaultLocale(): void
    {
        command('lang:sync --target de');

        $langFile = self::$languageTestPath . 'de/Sync.php';

        $this->assertFileExists($langFile);

        $langKeys = include $langFile;

        $this->assertIsArray($langKeys);
        $this->assertSame($this->expectedKeys, $langKeys);
    }

    public function testSyncWithLocaleOption(): void
    {
        command('lang:sync --locale ru --target de');

        $langFile = self::$languageTestPath . 'de/Sync.php';

        $this->assertFileExists($langFile);

        $langKeys = include $langFile;

        $this->assertIsArray($langKeys);
        $this->assertSame($this->expectedKeys, $langKeys);
    }

    public function testSyncWithExistTranslation(): void
    {
        // Save old values and add new keys from "en/Sync.php"
        // Add value from the old file "de/Sync.php" to new
        // Right sort as in "en/Sync.php"
        $expectedLangKeys = [
            'title'  => 'Default title (old)',
            'status' => [
                'error'    => 'Error! (old)',
                'done'     => 'Sync.status.done',
                'critical' => 'Critical! (old)',
            ],
            'description' => 'Sync.description',
            'empty_array' => [],
            'more'        => [
                'nested' => [
                    'key' => 'More nested key... (old)',
                ],
            ],
        ];

        $lang = <<<'TEXT_WRAP'
            <?php

            return [
                'status' => [
                    'critical' => 'Critical! (old)',
                    'error'    => 'Error! (old)',
                ],
                'skip'        => 'skip this value',
                'title'  => 'Default title (old)',
                'more'        => [
                    'nested' => [
                        'key' => 'More nested key... (old)',
                    ],
                ],
                'empty_array' => [],
            ];
            TEXT_WRAP;

        $langFile = self::$languageTestPath . 'de/Sync.php';

        mkdir(self::$languageTestPath . 'de', 0755);
        file_put_contents($langFile, $lang);

        command('lang:sync --target de');

        $this->assertFileExists($langFile);

        $langKeys = include $langFile;

        $this->assertIsArray($langKeys);
        $this->assertSame($expectedLangKeys, $langKeys);
    }

    public function testSyncWithIncorrectLocaleOption(): void
    {
        command('lang:sync --locale test_locale_incorrect --target de');

        $this->assertStringContainsString('is not supported', $this->getStreamFilterBuffer());
    }

    public function testSyncWithNullableOriginalLangValue(): void
    {
        $langWithNullValue = <<<'TEXT_WRAP'
            <?php

            return [
                'nullable' => null,
            ];
            TEXT_WRAP;

        file_put_contents(self::$languageTestPath . self::$locale . '/SyncInvalid.php', $langWithNullValue);
        ob_get_flush();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/Only "array" or "string" is allowed/');

        command('lang:sync --target de');
    }

    public function testSyncWithIntegerOriginalLangValue(): void
    {
        $this->resetStreamFilterBuffer();

        $langWithIntegerValue = <<<'TEXT_WRAP'
            <?php

            return [
                'integer' => 1000,
            ];
            TEXT_WRAP;

        file_put_contents(self::$languageTestPath . self::$locale . '/SyncInvalid.php', $langWithIntegerValue);
        ob_get_flush();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/Only "array" or "string" is allowed/');

        command('lang:sync --target de');
    }

    public function testSyncWithIncorrectTargetOption(): void
    {
        command('lang:sync --locale en --target test_locale_incorrect');

        $this->assertStringContainsString('is not supported', $this->getStreamFilterBuffer());
    }

    public function testProcessWithInvalidOption(): void
    {
        $langPath = SUPPORTPATH . 'Language';
        $command  = new LocalizationSync(service('logger'), service('commands'));
        $this->setPrivateProperty($command, 'languagePath', $langPath);
        $runner = self::getPrivateMethodInvoker($command, 'process');

        $status = $runner('de', 'jp');

        $this->assertSame(EXIT_ERROR, $status);
        $this->assertStringContainsString('Error: The "ROOTPATH/tests/_support/Language/de" directory was not found.', $this->getStreamFilterBuffer());

        chmod($langPath, 0544);
        $status = $runner('en', 'jp');
        chmod($langPath, 0775);

        $this->assertSame(EXIT_ERROR, $status);
        $this->assertStringContainsString('Error: The target directory "ROOTPATH/tests/_support/Language/jp" cannot be accessed.', $this->getStreamFilterBuffer());
    }

    private function makeLanguageFiles(): void
    {
        $lang = <<<'TEXT_WRAP'
            <?php

            return [
                'title'  => 'Default title',
                'status' => [
                    'error'    => 'Error!',
                    'done'     => 'Done!',
                    'critical' => 'Critical!',
                ],
                'description' => '',
                'empty_array' => [],
                'more'        => [
                    'nested' => [
                        'key' => 'More nested key...',
                    ],
                ],
            ];
            TEXT_WRAP;

        file_put_contents(self::$languageTestPath . self::$locale . '/Sync.php', $lang);
        file_put_contents(self::$languageTestPath . 'ru/Sync.php', $lang);
    }

    private function clearGeneratedFiles(): void
    {
        $files = [
            self::$languageTestPath . self::$locale . '/Sync.php',
            self::$languageTestPath . self::$locale . '/SyncInvalid.php',
            self::$languageTestPath . 'ru/Sync.php',
        ];

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        if (is_dir(self::$languageTestPath . 'de')) {
            delete_files(self::$languageTestPath . 'de');
            rmdir(self::$languageTestPath . 'de');
        }
    }
}
