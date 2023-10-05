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

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockLanguage;
use Config\Services;
use InvalidArgumentException;
use MessageFormatter;
use Tests\Support\Language\SecondMockLanguage;

/**
 * @internal
 *
 * @group Others
 */
final class LanguageTest extends CIUnitTestCase
{
    private Language $lang;

    protected function setUp(): void
    {
        $this->lang = new MockLanguage('en');
    }

    public function testReturnsStringWithNoFileInMessage(): void
    {
        $this->assertSame('something', $this->lang->getLine('something'));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/3822
     */
    public function testReturnParsedStringWithNoFileInMessage(): void
    {
        $this->lang->setLocale('en-GB');

        $line = '{price, number, currency}';

        $this->assertSame('£7.41', $this->lang->getLine($line, ['price' => '7.41']));
        $this->assertSame('£7.41', lang($line, ['price' => '7.41'], 'en-GB'));
    }

    public function testGetLineReturnsLine(): void
    {
        $this->lang->setData('books', [
            'bookSaved'  => 'We kept the book free from the boogeyman',
            'booksSaved' => 'We saved some more',
        ]);

        $this->assertSame('We saved some more', $this->lang->getLine('books.booksSaved'));
    }

    public function testGetLineReturnsFallbackLine(): void
    {
        $this->lang
            ->setLocale('en-US')
            ->setData('equivalent', [
                'touchWood'   => 'touch wood',
                'lieOfLand'   => 'lie of the land',
                'leaseOfLife' => 'a new lease of life',
                'slowcoach'   => 'slowcoach',
            ], 'en')
            ->setData('equivalent', [
                'lieOfLand' => 'lay of the land',
                'slowcoach' => 'slowpoke',
            ], 'en-US');

        $this->assertSame('lay of the land', $this->lang->getLine('equivalent.lieOfLand'));
        $this->assertSame('slowpoke', $this->lang->getLine('equivalent.slowcoach'));
        $this->assertSame('a new lease of life', $this->lang->getLine('equivalent.leaseOfLife'));
        $this->assertSame('touch wood', $this->lang->getLine('equivalent.touchWood'));
        $this->assertSame('equivalent.unknown', $this->lang->getLine('equivalent.unknown'));
    }

    public function testGetLineArrayReturnsLineArray(): void
    {
        $this->lang->setData('books', [
            'booksList' => [
                'The Boogeyman',
                'We Saved',
            ],
        ]);

        $this->assertSame([
            'The Boogeyman',
            'We Saved',
        ], $this->lang->getLine('books.booksList'));
    }

    public function testGetLineFormatsMessage(): void
    {
        // No intl extension? then we can't test this - go away....
        if (! class_exists(MessageFormatter::class)) {
            $this->markTestSkipped('No intl support.');
        }

        $this->lang->setData('books', [
            'bookCount' => '{0, number, integer} books have been saved.',
        ]);

        $this->assertSame('45 books have been saved.', $this->lang->getLine('books.bookCount', [91 / 2]));
    }

    public function testGetLineArrayFormatsMessages(): void
    {
        // No intl extension? Then we can't test this - go away...
        if (! class_exists(MessageFormatter::class)) {
            $this->markTestSkipped('No intl support.');
        }

        $this->lang->setData('books', [
            'bookList' => [
                '{0, number, integer} related books.',
            ],
        ]);

        $this->assertSame(['45 related books.'], $this->lang->getLine('books.bookList', [91 / 2]));
    }

    /**
     * @see https://github.com/codeigniter4/shield/issues/851
     */
    public function testGetLineInvalidFormatMessage(): void
    {
        // No intl extension? then we can't test this - go away....
        if (! class_exists(MessageFormatter::class)) {
            $this->markTestSkipped('No intl support.');
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid message format: "تم الكشف عن كلمة المرور {0} بسبب اختراق البيانات وشوهدت {1 ، عدد} مرة في {2} في كلمات المرور المخترقة.", args: "password,hits,wording"'
        );

        $this->lang->setLocale('ar');

        $this->lang->setData('Auth', [
            'errorPasswordPwned' => 'تم الكشف عن كلمة المرور {0} بسبب اختراق البيانات وشوهدت {1 ، عدد} مرة في {2} في كلمات المرور المخترقة.',
        ]);

        $this->lang->getLine('Auth.errorPasswordPwned', ['password', 'hits', 'wording']);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/891
     */
    public function testLangAllowsOtherLocales(): void
    {
        $str1 = lang('Language.languageGetLineInvalidArgumentException', [], 'en');
        $str2 = lang('Language.languageGetLineInvalidArgumentException', [], 'ru');

        $this->assertSame('Get line must be a string or array of strings.', $str1);
        $this->assertSame('Whatever this would be, translated', $str2);
    }

    public function testLangDoesntFormat(): void
    {
        $this->lang->disableIntlSupport();

        $this->lang->setData('books', [
            'bookList' => [
                '{0, number, integer} related books.',
            ],
        ]);

        $this->assertSame(['{0, number, integer} related books.'], $this->lang->getLine('books.bookList', [15]));
    }

    public function testLanguageDuplicateKey(): void
    {
        $this->lang = new Language('en');
        $this->assertSame('These are not the droids you are looking for', $this->lang->getLine('More.strongForce', []));
        $this->assertSame('I have a very bad feeling about this', $this->lang->getLine('More.cannotMove', []));
        $this->assertSame('Could not move file "{0}" to "{1}". Reason: {2}', $this->lang->getLine('Files.cannotMove', []));
        $this->assertSame('I have a very bad feeling about this', $this->lang->getLine('More.cannotMove', []));
    }

    public function testLanguageFileLoading(): void
    {
        $this->lang = new SecondMockLanguage('en');

        $this->lang->loadem('More', 'en');
        $this->assertContains('More', $this->lang->loaded());

        $this->lang->loadem('More', 'en');
        $this->assertCount(1, $this->lang->loaded()); // should only be there once
    }

    public function testLanguageFileLoadingReturns(): void
    {
        $this->lang = new SecondMockLanguage('en');

        $result = $this->lang->loadem('More', 'en', true);
        $this->assertNotContains('More', $this->lang->loaded());
        $this->assertCount(3, $result);

        $this->lang->loadem('More', 'en');
        $this->assertContains('More', $this->lang->loaded());
        $this->assertCount(1, $this->lang->loaded());
    }

    public function testLanguageSameKeyAndFileName(): void
    {
        // first file data | example.message
        $this->lang->setData('example', ['message' => 'This is an example message']);

        // force loading data into file Example
        $this->assertSame('This is an example message', $this->lang->getLine('example.message'));

        // second file data | another.example
        $this->lang->setData('another', ['example' => 'Another example']);

        $this->assertSame('Another example', $this->lang->getLine('another.example'));
    }

    public function testGetLocale(): void
    {
        $this->lang = Services::language('en', false);
        $this->assertSame('en', $this->lang->getLocale());
    }

    public function testPrioritizedLocator(): void
    {
        // this should load the replacement bundle of messages
        $message = lang('Core.missingExtension', [], 'en');
        $this->assertSame('The framework needs the following extension(s) installed and loaded: "{0}".', $message);
        // and we should have our new message too
        $this->assertSame('billions and billions', lang('Core.bazillion', [], 'en'));
    }

    public static function provideBundleUniqueKeys(): iterable
    {
        return [
            ['CLI'],
            ['Cache'],
            ['Cast'],
            ['Core'],
            ['Database'],
            ['Files'],
            ['Filters'],
            ['Format'],
            ['HTTP'],
            ['Images'],
            ['Log'],
            ['Migrations'],
            ['Number'],
            ['Pager'],
            ['RESTful'],
            ['Router'],
            ['Session'],
            ['Test'],
            ['Time'],
            ['Validation'],
            ['View'],
        ];
    }

    /**
     * There's not a whole lot that can be done with message bundles,
     * but we can at least try loading them ... more accurate code coverage?
     *
     * @dataProvider provideBundleUniqueKeys
     *
     * @param mixed $bundle
     */
    public function testBundleUniqueKeys($bundle): void
    {
        $messages = require SYSTEMPATH . 'Language/en/' . $bundle . '.php';
        $this->assertGreaterThan(0, count($messages));
    }

    // Testing base locale vs variants

    public function testBaseFallbacks(): void
    {
        $this->lang = Services::language('en-ZZ', false);
        // key is in both base and variant; should pick variant
        $this->assertSame("It's made of cheese", $this->lang->getLine('More.notaMoon'));

        // key is in base but not variant; should pick base
        $this->assertSame('I have a very bad feeling about this', $this->lang->getLine('More.cannotMove'));

        // key is in variant but not base; should pick variant
        $this->assertSame('There is no try', $this->lang->getLine('More.wisdom'));

        // key isn't in either base or variant; should return bad key
        $this->assertSame('More.shootMe', $this->lang->getLine('More.shootMe'));
    }

    /**
     * Test if after using lang() with a locale the Language class keep the locale after return the $line
     */
    public function testLangKeepLocale(): void
    {
        $this->lang = Services::language('en', true);

        lang('Language.languageGetLineInvalidArgumentException');
        $this->assertSame('en', $this->lang->getLocale());

        lang('Language.languageGetLineInvalidArgumentException', [], 'ru');
        $this->assertSame('en', $this->lang->getLocale());

        lang('Language.languageGetLineInvalidArgumentException');
        $this->assertSame('en', $this->lang->getLocale());
    }

    /**
     * Testing base locale vs variants, with fallback to English.
     *
     * Key	en	ab	ac-CD
     * none	N	N	N
     * one	N	N	Y
     * two	N	Y	N
     * tre	N	Y	Y
     * for	Y	N	N
     * fiv	Y	N	Y
     * six	Y	Y	N
     * sev	Y	Y	Y
     */
    public function testAllTheWayFallbacks(): void
    {
        $this->lang = Services::language('ab-CD', false);
        $this->assertSame('Allin.none', $this->lang->getLine('Allin.none'));
        $this->assertSame('Pyramid of Giza', $this->lang->getLine('Allin.one'));
        $this->assertSame('gluttony', $this->lang->getLine('Allin.two'));
        $this->assertSame('Colossus of Rhodes', $this->lang->getLine('Allin.tre'));
        $this->assertSame('four calling birds', $this->lang->getLine('Allin.for'));
        $this->assertSame('Temple of Artemis', $this->lang->getLine('Allin.fiv'));
        $this->assertSame('envy', $this->lang->getLine('Allin.six'));
        $this->assertSame('Hanging Gardens of Babylon', $this->lang->getLine('Allin.sev'));
    }

    public function testLanguageNestedArrayDefinition(): void
    {
        $this->lang = new SecondMockLanguage('en');
        $this->lang->loadem('Nested', 'en');

        $this->assertSame('e', $this->lang->getLine('Nested.a.b.c.d'));
    }

    public function testLanguageKeySeparatedByDot(): void
    {
        $this->lang = new SecondMockLanguage('en');
        $this->lang->loadem('Foo', 'en');

        $this->assertSame('The fieldname field is very short.', $this->lang->getLine('Foo.bar.min_length1', ['field' => 'fieldname']));
        $this->assertSame('The fieldname field is very short.', $this->lang->getLine('Foo.baz.min_length3.short', ['field' => 'fieldname']));
    }
}
