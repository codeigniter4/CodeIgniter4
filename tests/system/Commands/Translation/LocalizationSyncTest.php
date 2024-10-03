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

use CodeIgniter\Test\CIUnitTestCase;
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

    private static string $locale;
    private static string $languageTestPath;

    /**
     * @var array<string, array<string,mixed>|string|null>
     */
    private array $expectedKeys = [
        'a' => 'Sync.a',
        'b' => 'Sync.b',
        'c' => 'Sync.c',
        'd' => [],
        'e' => 'Sync.e',
        'f' => [
            'g' => 'Sync.f.g',
            'h' => [
                'i' => 'Sync.f.h.i',
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        config(App::class)->supportedLocales = ['en', 'ru', 'test'];

        self::$locale           = Locale::getDefault();
        self::$languageTestPath = SUPPORTPATH . 'Language' . DIRECTORY_SEPARATOR;
        $this->makeLanguageFiles();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->clearGeneratedFiles();
    }

    public function testSyncDefaultLocale(): void
    {
        command('lang:sync --target test');

        $langFile = self::$languageTestPath . 'test/Sync.php';

        $this->assertFileExists($langFile);

        $langKeys = include $langFile;

        $this->assertIsArray($langKeys);
        $this->assertSame($this->expectedKeys, $langKeys);
    }

    public function testSyncWithLocaleOption(): void
    {
        command('lang:sync --locale ru --target test');

        $langFile = self::$languageTestPath . 'test/Sync.php';

        $this->assertFileExists($langFile);

        $langKeys = include $langFile;

        $this->assertIsArray($langKeys);
        $this->assertSame($this->expectedKeys, $langKeys);
    }

    public function testSyncWithExistTranslation(): void
    {
        // First run, add new keys
        command('lang:sync --target test');

        $langFile = self::$languageTestPath . 'test/Sync.php';

        $this->assertFileExists($langFile);

        $langKeys = include $langFile;

        $this->assertIsArray($langKeys);
        $this->assertSame($this->expectedKeys, $langKeys);

        // Second run, save old keys
        $oldLangKeys = [
            'a' => 'old value 1',
            'b' => 2000,
            'c' => null,
            'd' => [],
            'e' => '',
            'f' => [
                'g' => 'old value 2',
                'h' => [
                    'i' => 'old value 3',
                ],
            ],
        ];

        $lang = <<<'TEXT_WRAP'
            <?php

            return [
                'a' => 'old value 1',
                'b' => 2000,
                'c' => null,
                'd' => [],
                'e' => '',
                'f' => [
                    'g' => 'old value 2',
                    'h' => [
                        'i' => 'old value 3',
                    ],
                ],
            ];
            TEXT_WRAP;

        file_put_contents(self::$languageTestPath . 'test/Sync.php', $lang);

        command('lang:sync --target test');

        $langFile = self::$languageTestPath . 'test/Sync.php';

        $this->assertFileExists($langFile);

        $langKeys = include $langFile;
        $this->assertIsArray($langKeys);
        $this->assertSame($oldLangKeys, $langKeys);
    }

    public function testSyncWithIncorrectLocaleOption(): void
    {
        command('lang:sync --locale test_locale_incorrect --target test');

        $this->assertStringContainsString('is not supported', $this->getStreamFilterBuffer());
    }

    public function testSyncWithIncorrectTargetOption(): void
    {
        command('lang:sync --locale en --target test_locale_incorrect');

        $this->assertStringContainsString('is not supported', $this->getStreamFilterBuffer());
    }

    private function makeLanguageFiles(): void
    {
        $lang = <<<'TEXT_WRAP'
            <?php

            return [
                'a' => 'value 1',
                'b' => 2,
                'c' => null,
                'd' => [],
                'e' => '',
                'f' => [
                    'g' => 'value 2',
                    'h' => [
                        'i' => 'value 3',
                    ],
                ],
            ];
            TEXT_WRAP;

        file_put_contents(self::$languageTestPath . self::$locale . '/Sync.php', $lang);
        file_put_contents(self::$languageTestPath . 'ru/Sync.php', $lang);
    }

    private function clearGeneratedFiles(): void
    {
        if (is_file(self::$languageTestPath . self::$locale . '/Sync.php')) {
            unlink(self::$languageTestPath . self::$locale . '/Sync.php');
        }

        if (is_file(self::$languageTestPath . 'ru/Sync.php')) {
            unlink(self::$languageTestPath . 'ru/Sync.php');
        }

        if (is_dir(self::$languageTestPath . 'test')) {
            $files = glob(self::$languageTestPath . 'test/*', GLOB_MARK);

            foreach ($files as $file) {
                unlink($file);
            }

            rmdir(self::$languageTestPath . 'test');
        }
    }
}
