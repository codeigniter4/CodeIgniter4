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

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Config\App;
use Config\Services;
use Locale;

/**
 * @internal
 */
final class LocalizationFinderTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private static string $locale;
    private static string $languageTestPath;

    protected function setUp(): void
    {
        parent::setUp();
        self::$locale           = Locale::getDefault();
        self::$languageTestPath = SUPPORTPATH . 'Language/';
        $this->clearGeneratedFiles();
    }

    public function testUpdateDefaultLocale(): void
    {
        @mkdir(self::$languageTestPath . self::$locale, 0777, true);
        Services::commands()->run('lang:find', [
            'dir' => 'Controllers/Translation',
        ]);
        $this->realizeAssertion();
        $this->clearGeneratedFiles();
    }

    public function testUpdateWithLocaleOption(): void
    {
        self::$locale = config(App::class)->supportedLocales[0];
        @mkdir(self::$languageTestPath . self::$locale, 0777, true);
        Services::commands()->run('lang:find', [
            'dir'    => 'Controllers/Translation',
            'locale' => self::$locale,
        ]);
        $this->realizeAssertion();
        $this->clearGeneratedFiles();
    }

    public function testUpdateWithIncorrectLocaleOption(): void
    {
        self::$locale = 'test_locale_incorrect';
        @mkdir(self::$languageTestPath . self::$locale, 0777, true);
        $status = Services::commands()->run('lang:find', [
            'dir'    => 'Controllers/Translation',
            'locale' => self::$locale,
        ]);
        $this->assertSame($status, -1);
        $this->clearGeneratedFiles();
    }

    public function testUpdateWithEmptyDirOption(): void
    {
        @mkdir(self::$languageTestPath . self::$locale, 0777, true);
        Services::commands()->run('lang:find', []);
        $this->realizeAssertion();
        $this->clearGeneratedFiles();
    }

    public function testUpdateWithIncorrectDirOption(): void
    {
        @mkdir(self::$languageTestPath . self::$locale, 0777, true);
        $status = Services::commands()->run('lang:find', [
            'dir' => 'Controllers/Translation/NotExistFolder',
        ]);
        $this->assertSame($status, -1);
        $this->clearGeneratedFiles();
    }

    public function testShowNewTranslation(): void
    {
        @mkdir(self::$languageTestPath . self::$locale, 0777, true);
        Services::commands()->run('lang:find', [
            'dir'      => 'Controllers/Translation',
            'show-new' => null,
        ]);
        $this->assertStringContainsString($this->getActualTableWithNewKeys(), $this->getStreamFilterBuffer());
        $this->clearGeneratedFiles();
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

    private function getActualTableWithNewKeys(): string
    {
        return <<<'TEXT'
            +------------------+----------------------------------------------------+
            | File             | Key                                                |
            +------------------+----------------------------------------------------+
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
            TEXT;
    }

    private function realizeAssertion(): void
    {
        $this->assertFileExists(self::$languageTestPath . self::$locale . '/TranslationOne.php');
        $this->assertFileExists(self::$languageTestPath . self::$locale . '/TranslationThree.php');

        $translationOneKeys   = require self::$languageTestPath . self::$locale . '/TranslationOne.php';
        $translationThreeKeys = require self::$languageTestPath . self::$locale . '/TranslationThree.php';

        $this->assertSame($translationOneKeys, $this->getActualTranslationOneKeys());
        $this->assertSame($translationThreeKeys, $this->getActualTranslationThreeKeys());
    }

    private function clearGeneratedFiles(): void
    {
        if (is_file(self::$languageTestPath . self::$locale . '/TranslationOne.php')) {
            unlink(self::$languageTestPath . self::$locale . '/TranslationOne.php');
        }

        if (is_file(self::$languageTestPath . self::$locale . '/TranslationThree.php')) {
            unlink(self::$languageTestPath . self::$locale . '/TranslationThree.php');
        }
    }
}
