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
final class LocalizationFinderTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private static string $locale;
    private static string $languageTestPath;

    protected function setUp(): void
    {
        parent::setUp();
        self::$locale           = Locale::getDefault();
        self::$languageTestPath = SUPPORTPATH . 'Language' . DIRECTORY_SEPARATOR;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->clearGeneratedFiles();
    }

    public function testUpdateDefaultLocale(): void
    {
        $this->makeLocaleDirectory();

        command('lang:find --dir Translation');

        $this->assertTranslationsExistAndHaveTranslatedKeys();
    }

    public function testUpdateWithLocaleOption(): void
    {
        self::$locale = config(App::class)->supportedLocales[0];
        $this->makeLocaleDirectory();

        command('lang:find --dir Translation --locale ' . self::$locale);

        $this->assertTranslationsExistAndHaveTranslatedKeys();
    }

    public function testUpdateWithIncorrectLocaleOption(): void
    {
        self::$locale = 'test_locale_incorrect';
        $this->makeLocaleDirectory();

        $status = service('commands')->run('lang:find', [
            'dir'    => 'Translation',
            'locale' => self::$locale,
        ]);

        $this->assertSame(EXIT_USER_INPUT, $status);
    }

    public function testUpdateWithEmptyDirOption(): void
    {
        $this->makeLocaleDirectory();

        command('lang:find');

        $this->assertTranslationsExistAndHaveTranslatedKeys();
    }

    public function testUpdateWithIncorrectDirOption(): void
    {
        $this->makeLocaleDirectory();

        $status = service('commands')->run('lang:find', [
            'dir' => 'Translation/NotExistFolder',
        ]);

        $this->assertSame(EXIT_USER_INPUT, $status);
    }

    public function testShowNewTranslation(): void
    {
        $this->makeLocaleDirectory();

        command('lang:find --dir Translation --show-new');

        $this->assertStringContainsString($this->getActualTableWithNewKeys(), $this->getStreamFilterBuffer());
    }

    public function testShowBadTranslation(): void
    {
        $this->makeLocaleDirectory();

        command('lang:find --dir Translation --verbose');

        $this->assertStringContainsString($this->getActualTableWithBadKeys(), $this->getStreamFilterBuffer());
    }

    private function getActualTranslationOneKeys(): array
    {
        return [
            'title'                  => 'TranslationOne.title',
            'DESCRIPTION'            => 'TranslationOne.DESCRIPTION',
            'subTitle'               => 'TranslationOne.subTitle',
            'overflow_style'         => 'TranslationOne.overflow_style',
            'metaTags'               => 'TranslationOne.metaTags',
            'Copyright'              => 'TranslationOne.Copyright',
            'last_operation_success' => 'TranslationOne.last_operation_success',
        ];
    }

    private function getActualTranslationThreeKeys(): array
    {
        return [
            'alerts' => [
                'created'       => 'TranslationThree.alerts.created',
                'failed_insert' => 'TranslationThree.alerts.failed_insert',
                'CANCELED'      => 'TranslationThree.alerts.CANCELED',
                'missing_keys'  => 'TranslationThree.alerts.missing_keys',
                'Updated'       => 'TranslationThree.alerts.Updated',
                'DELETED'       => 'TranslationThree.alerts.DELETED',
            ],
            'formFields' => [
                'new' => [
                    'name'      => 'TranslationThree.formFields.new.name',
                    'TEXT'      => 'TranslationThree.formFields.new.TEXT',
                    'short_tag' => 'TranslationThree.formFields.new.short_tag',
                ],
                'edit' => [
                    'name'      => 'TranslationThree.formFields.edit.name',
                    'TEXT'      => 'TranslationThree.formFields.edit.TEXT',
                    'short_tag' => 'TranslationThree.formFields.edit.short_tag',
                ],
            ],
            'formErrors' => [
                'edit' => [
                    'empty_name'        => 'TranslationThree.formErrors.edit.empty_name',
                    'INVALID_TEXT'      => 'TranslationThree.formErrors.edit.INVALID_TEXT',
                    'missing_short_tag' => 'TranslationThree.formErrors.edit.missing_short_tag',
                ],
            ],
        ];
    }

    private function getActualTranslationFourKeys(): array
    {
        return [
            'dashed' => [
                'key-with-dash'     => 'Translation-Four.dashed.key-with-dash',
                'key-with-dash-two' => 'Translation-Four.dashed.key-with-dash-two',
            ],
        ];
    }

    private function getActualTableWithNewKeys(): string
    {
        return <<<'TEXT_WRAP'
            +------------------+----------------------------------------------------+
            | File             | Key                                                |
            +------------------+----------------------------------------------------+
            | Translation-Four | Translation-Four.dashed.key-with-dash              |
            | Translation-Four | Translation-Four.dashed.key-with-dash-two          |
            | TranslationOne   | TranslationOne.Copyright                           |
            | TranslationOne   | TranslationOne.DESCRIPTION                         |
            | TranslationOne   | TranslationOne.last_operation_success              |
            | TranslationOne   | TranslationOne.metaTags                            |
            | TranslationOne   | TranslationOne.overflow_style                      |
            | TranslationOne   | TranslationOne.subTitle                            |
            | TranslationOne   | TranslationOne.title                               |
            | TranslationThree | TranslationThree.alerts.CANCELED                   |
            | TranslationThree | TranslationThree.alerts.DELETED                    |
            | TranslationThree | TranslationThree.alerts.Updated                    |
            | TranslationThree | TranslationThree.alerts.created                    |
            | TranslationThree | TranslationThree.alerts.failed_insert              |
            | TranslationThree | TranslationThree.alerts.missing_keys               |
            | TranslationThree | TranslationThree.formErrors.edit.INVALID_TEXT      |
            | TranslationThree | TranslationThree.formErrors.edit.empty_name        |
            | TranslationThree | TranslationThree.formErrors.edit.missing_short_tag |
            | TranslationThree | TranslationThree.formFields.edit.TEXT              |
            | TranslationThree | TranslationThree.formFields.edit.name              |
            | TranslationThree | TranslationThree.formFields.edit.short_tag         |
            | TranslationThree | TranslationThree.formFields.new.TEXT               |
            | TranslationThree | TranslationThree.formFields.new.name               |
            | TranslationThree | TranslationThree.formFields.new.short_tag          |
            +------------------+----------------------------------------------------+
            TEXT_WRAP;
    }

    private function getActualTableWithBadKeys(): string
    {
        return <<<'TEXT_WRAP'
            +------------------------+--------------------------------------------------------+
            | Bad Key                | Filepath                                               |
            +------------------------+--------------------------------------------------------+
            | ..invalid_nested_key.. | tests/_support/Services/Translation/TranslationTwo.php |
            | ..invalid_nested_key.. | tests/_support/Services/Translation/TranslationTwo.php |
            | .invalid_key           | tests/_support/Services/Translation/TranslationTwo.php |
            | .invalid_key           | tests/_support/Services/Translation/TranslationTwo.php |
            | TranslationTwo         | tests/_support/Services/Translation/TranslationTwo.php |
            | TranslationTwo         | tests/_support/Services/Translation/TranslationTwo.php |
            | TranslationTwo.        | tests/_support/Services/Translation/TranslationTwo.php |
            | TranslationTwo.        | tests/_support/Services/Translation/TranslationTwo.php |
            | TranslationTwo...      | tests/_support/Services/Translation/TranslationTwo.php |
            | TranslationTwo...      | tests/_support/Services/Translation/TranslationTwo.php |
            +------------------------+--------------------------------------------------------+
            TEXT_WRAP;
    }

    private function assertTranslationsExistAndHaveTranslatedKeys(): void
    {
        $this->assertFileExists(self::$languageTestPath . self::$locale . '/TranslationOne.php');
        $this->assertFileExists(self::$languageTestPath . self::$locale . '/TranslationThree.php');
        $this->assertFileExists(self::$languageTestPath . self::$locale . '/Translation-Four.php');

        $translationOneKeys   = require self::$languageTestPath . self::$locale . '/TranslationOne.php';
        $translationThreeKeys = require self::$languageTestPath . self::$locale . '/TranslationThree.php';
        $translationFourKeys  = require self::$languageTestPath . self::$locale . '/Translation-Four.php';

        $this->assertSame($translationOneKeys, $this->getActualTranslationOneKeys());
        $this->assertSame($translationThreeKeys, $this->getActualTranslationThreeKeys());
        $this->assertSame($translationFourKeys, $this->getActualTranslationFourKeys());
    }

    private function makeLocaleDirectory(): void
    {
        @mkdir(self::$languageTestPath . self::$locale, 0777, true);
    }

    private function clearGeneratedFiles(): void
    {
        if (is_file(self::$languageTestPath . self::$locale . '/TranslationOne.php')) {
            unlink(self::$languageTestPath . self::$locale . '/TranslationOne.php');
        }

        if (is_file(self::$languageTestPath . self::$locale . '/TranslationThree.php')) {
            unlink(self::$languageTestPath . self::$locale . '/TranslationThree.php');
        }

        if (is_file(self::$languageTestPath . self::$locale . '/Translation-Four.php')) {
            unlink(self::$languageTestPath . self::$locale . '/Translation-Four.php');
        }

        if (is_dir(self::$languageTestPath . '/test_locale_incorrect')) {
            rmdir(self::$languageTestPath . '/test_locale_incorrect');
        }
    }
}
