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

namespace CodeIgniter\Language;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockLanguage;
use MessageFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Language\SecondMockLanguage;

/**
 * @internal
 */
#[Group('Others')]
final class LanguageTest extends CIUnitTestCase
{
    private Language $lang;

    protected function setUp(): void
    {
        $this->lang = new Language('en');
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
        $this->lang = new MockLanguage('en');

        $this->lang->setData('books', [
            'bookSaved'  => 'We kept the book free from the boogeyman',
            'booksSaved' => 'We saved some more',
        ]);

        $this->assertSame('We saved some more', $this->lang->getLine('books.booksSaved'));
    }

    public function testGetLineReturnsLineWithKeyWithDots(): void
    {
        $this->lang = new MockLanguage('en');

        $this->lang->setData('books', [
            'bookSaved.foo'      => 'We kept the book free from the boogeyman',
            'booksSaved.bar.baz' => 'We saved some more',
        ]);

        $this->assertSame(
            'We kept the book free from the boogeyman',
            $this->lang->getLine('books.bookSaved.foo')
        );
        $this->assertSame(
            'We saved some more',
            $this->lang->getLine('books.booksSaved.bar.baz')
        );
    }

    public function testGetLineCannotUseKeysWithLeadingDot(): void
    {
        $this->lang = new MockLanguage('en');

        $this->lang->setData('books', [
            '.bookSaved.foo.'      => 'We kept the book free from the boogeyman',
            '.booksSaved.bar.baz.' => 'We saved some more',
        ]);

        $this->assertSame(
            'books.bookSaved.foo', // Can't get the message.
            $this->lang->getLine('books.bookSaved.foo')
        );
        $this->assertSame(
            'books.booksSaved.bar.baz', // Can't get the message.
            $this->lang->getLine('books.booksSaved.bar.baz')
        );
    }

    public function testGetLineCannotUseKeysWithTrailingDot(): void
    {
        $this->lang = new MockLanguage('en');

        $this->lang->setData('books', [
            'bookSaved.foo.'      => 'We kept the book free from the boogeyman',
            'booksSaved.bar.baz.' => 'We saved some more',
        ]);

        $this->assertSame(
            'books.bookSaved.foo', // Can't get the message.
            $this->lang->getLine('books.bookSaved.foo')
        );
        $this->assertSame(
            'books.booksSaved.bar.baz', // Can't get the message.
            $this->lang->getLine('books.booksSaved.bar.baz')
        );
    }

    public function testGetLineReturnsFallbackLine(): void
    {
        $this->lang = new MockLanguage('en');

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
        $this->lang = new MockLanguage('en');

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

        $this->lang = new MockLanguage('en');

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

        $this->lang = new MockLanguage('en');

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

        $this->lang = new MockLanguage('en');

        $this->lang->setLocale('ar');

        $line = 'تم الكشف عن كلمة المرور {0} بسبب اختراق البيانات وشوهدت {1 ، عدد} مرة في {2} في كلمات المرور المخترقة.';
        $this->lang->setData('Auth', ['errorPasswordPwned' => $line]);

        $output = $this->lang->getLine('Auth.errorPasswordPwned', ['password', 'hits', 'wording']);

        $this->assertSame($line . "\n【Warning】Also, invalid string(s) was passed to the Language class. See log file for details.", $output);
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
        $this->lang = new MockLanguage('en');

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
        $lang = new SecondMockLanguage('en');

        $lang->loadem('More', 'en');
        $this->assertContains('More', $lang->loaded());

        $lang->loadem('More', 'en');
        $this->assertCount(1, $lang->loaded()); // should only be there once
    }

    public function testLanguageFileLoadingReturns(): void
    {
        $lang = new SecondMockLanguage('en');

        $result = $lang->loadem('More', 'en', true);
        $this->assertNotContains('More', $lang->loaded());
        $this->assertCount(3, $result);

        $lang->loadem('More', 'en');
        $this->assertContains('More', $lang->loaded());
        $this->assertCount(1, $lang->loaded());
    }

    public function testLanguageSameKeyAndFileName(): void
    {
        $lang = new MockLanguage('en');

        // first file data | example.message
        $lang->setData('example', ['message' => 'This is an example message']);

        // force loading data into file Example
        $this->assertSame('This is an example message', $lang->getLine('example.message'));

        // second file data | another.example
        $lang->setData('another', ['example' => 'Another example']);

        $this->assertSame('Another example', $lang->getLine('another.example'));
    }

    public function testGetLocale(): void
    {
        $this->lang = service('language', 'en', false);
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
     * @param mixed $bundle
     */
    #[DataProvider('provideBundleUniqueKeys')]
    public function testBundleUniqueKeys($bundle): void
    {
        $messages = require SYSTEMPATH . 'Language/en/' . $bundle . '.php';
        $this->assertGreaterThan(0, count($messages));
    }

    // Testing base locale vs variants

    public function testBaseFallbacks(): void
    {
        $this->lang = service('language', 'en-ZZ', false);
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
        $this->lang = service('language', 'en', true);

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
        $this->lang = service('language', 'ab-CD', false);
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
