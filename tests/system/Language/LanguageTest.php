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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\Support\Language\SecondMockLanguage;

/**
 * @internal
 */
#[Group('Others')]
final class LanguageTest extends CIUnitTestCase
{
    public function testReturnsStringWithNoFileInMessage(): void
    {
        $lang = new MockLanguage('en');
        $this->assertSame('something', $lang->getLine('something'));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/3822
     */
    public function testReturnParsedStringWithNoFileInMessage(): void
    {
        $lang = new MockLanguage('en');
        $lang->setLocale('en-GB');

        $line = '{price, number, currency}';

        $this->assertSame('£7.41', $lang->getLine($line, ['price' => '7.41']));
        $this->assertSame('£7.41', lang($line, ['price' => '7.41'], 'en-GB'));
    }

    public function testGetLineReturnsLine(): void
    {
        $lang = new MockLanguage('en');
        $lang->setData('books', [
            'bookSaved'  => 'We kept the book free from the boogeyman',
            'booksSaved' => 'We saved some more',
        ]);

        $this->assertSame('We saved some more', $lang->getLine('books.booksSaved'));
    }

    public function testGetLineReturnsLineWithKeyWithDots(): void
    {
        $lang = new MockLanguage('en');
        $lang->setData('books', [
            'bookSaved.foo'      => 'We kept the book free from the boogeyman',
            'booksSaved.bar.baz' => 'We saved some more',
        ]);

        $this->assertSame(
            'We kept the book free from the boogeyman',
            $lang->getLine('books.bookSaved.foo'),
        );
        $this->assertSame(
            'We saved some more',
            $lang->getLine('books.booksSaved.bar.baz'),
        );
    }

    public function testGetLineCannotUseKeysWithLeadingDot(): void
    {
        $lang = new MockLanguage('en');
        $lang->setData('books', [
            '.bookSaved.foo.'      => 'We kept the book free from the boogeyman',
            '.booksSaved.bar.baz.' => 'We saved some more',
        ]);

        $this->assertSame(
            'books.bookSaved.foo', // Can't get the message.
            $lang->getLine('books.bookSaved.foo'),
        );
        $this->assertSame(
            'books.booksSaved.bar.baz', // Can't get the message.
            $lang->getLine('books.booksSaved.bar.baz'),
        );
    }

    public function testGetLineCannotUseKeysWithTrailingDot(): void
    {
        $lang = new MockLanguage('en');
        $lang->setData('books', [
            'bookSaved.foo.'      => 'We kept the book free from the boogeyman',
            'booksSaved.bar.baz.' => 'We saved some more',
        ]);

        $this->assertSame(
            'books.bookSaved.foo', // Can't get the message.
            $lang->getLine('books.bookSaved.foo'),
        );
        $this->assertSame(
            'books.booksSaved.bar.baz', // Can't get the message.
            $lang->getLine('books.booksSaved.bar.baz'),
        );
    }

    public function testGetLineReturnsFallbackLine(): void
    {
        $lang = new MockLanguage('en');
        $lang
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

        $this->assertSame('lay of the land', $lang->getLine('equivalent.lieOfLand'));
        $this->assertSame('slowpoke', $lang->getLine('equivalent.slowcoach'));
        $this->assertSame('a new lease of life', $lang->getLine('equivalent.leaseOfLife'));
        $this->assertSame('touch wood', $lang->getLine('equivalent.touchWood'));
        $this->assertSame('equivalent.unknown', $lang->getLine('equivalent.unknown'));
    }

    public function testGetLineArrayReturnsLineArray(): void
    {
        $lang = new MockLanguage('en');
        $lang->setData('books', ['booksList' => ['The Boogeyman', 'We Saved']]);

        $this->assertSame(['The Boogeyman', 'We Saved'], $lang->getLine('books.booksList'));
    }

    #[RequiresPhpExtension('intl')]
    public function testGetLineFormatsMessage(): void
    {
        $lang = new MockLanguage('en');
        $lang->setData('books', [
            'bookCount' => '{0, number, integer} books have been saved.',
        ]);

        $this->assertSame('45 books have been saved.', $lang->getLine('books.bookCount', [91 / 2]));
    }

    #[RequiresPhpExtension('intl')]
    public function testGetLineArrayFormatsMessages(): void
    {
        $lang = new MockLanguage('en');
        $lang->setData('books', [
            'bookList' => [
                '{0, number, integer} related books.',
            ],
        ]);

        $this->assertSame(['45 related books.'], $lang->getLine('books.bookList', [91 / 2]));
    }

    /**
     * @see https://github.com/codeigniter4/shield/issues/851
     */
    #[RequiresPhpExtension('intl')]
    public function testGetLineInvalidFormatMessage(): void
    {
        $lang = new MockLanguage('en');
        $lang->setLocale('ar');

        $line = 'تم الكشف عن كلمة المرور {0} بسبب اختراق البيانات وشوهدت {1 ، عدد} مرة في {2} في كلمات المرور المخترقة.';
        $lang->setData('Auth', ['errorPasswordPwned' => $line]);

        $output = $lang->getLine('Auth.errorPasswordPwned', ['password', 'hits', 'wording']);

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
        $lang = new MockLanguage('en');
        $lang->disableIntlSupport();
        $lang->setData('books', [
            'bookList' => [
                '{0, number, integer} related books.',
            ],
        ]);

        $this->assertSame(['{0, number, integer} related books.'], $lang->getLine('books.bookList', [15]));
    }

    public function testLanguageDuplicateKey(): void
    {
        $lang = new Language('en');
        $this->assertSame('These are not the droids you are looking for', $lang->getLine('More.strongForce', []));
        $this->assertSame('I have a very bad feeling about this', $lang->getLine('More.cannotMove', []));
        $this->assertSame('Could not move file "{0}" to "{1}". Reason: {2}', $lang->getLine('Files.cannotMove', []));
        $this->assertSame('I have a very bad feeling about this', $lang->getLine('More.cannotMove', []));
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
        $lang = service('language', 'en', false);
        $this->assertSame('en', $lang->getLocale());
    }

    public function testPrioritizedLocator(): void
    {
        // this should load the replacement bundle of messages
        $message = lang('Core.missingExtension', [], 'en');
        $this->assertSame('The framework needs the following extension(s) installed and loaded: "{0}".', $message);
        // and we should have our new message too
        $this->assertSame('billions and billions', lang('Core.bazillion', [], 'en'));
    }

    /**
     * There's not a whole lot that can be done with message bundles,
     * but we can at least try loading them ... more accurate code coverage?
     */
    #[DataProvider('provideBundleUniqueKeys')]
    public function testBundleUniqueKeys(string $bundle): void
    {
        $messages = require SYSTEMPATH . 'Language/en/' . $bundle . '.php';
        $this->assertIsArray($messages);
        $this->assertGreaterThan(0, count($messages));
    }

    /**
     * @return iterable<int, array{0: string}>
     */
    public static function provideBundleUniqueKeys(): iterable
    {
        yield from [
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

    public function testBaseLocaleVsVariants(): void
    {
        $lang = service('language', 'en-ZZ', false);
        // key is in both base and variant; should pick variant
        $this->assertSame("It's made of cheese", $lang->getLine('More.notaMoon'));

        // key is in base but not variant; should pick base
        $this->assertSame('I have a very bad feeling about this', $lang->getLine('More.cannotMove'));

        // key is in variant but not base; should pick variant
        $this->assertSame('There is no try', $lang->getLine('More.wisdom'));

        // key isn't in either base or variant; should return bad key
        $this->assertSame('More.shootMe', $lang->getLine('More.shootMe'));
    }

    /**
     * Test if after using lang() with a locale the Language class keep the locale after return the $line
     */
    public function testLangKeepLocale(): void
    {
        $lang = service('language', 'en', true);

        lang('Language.languageGetLineInvalidArgumentException');
        $this->assertSame('en', $lang->getLocale());

        lang('Language.languageGetLineInvalidArgumentException', [], 'ru');
        $this->assertSame('en', $lang->getLocale());

        lang('Language.languageGetLineInvalidArgumentException');
        $this->assertSame('en', $lang->getLocale());
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
        $lang = service('language', 'ab-CD', false);
        $this->assertSame('Allin.none', $lang->getLine('Allin.none'));
        $this->assertSame('Pyramid of Giza', $lang->getLine('Allin.one'));
        $this->assertSame('gluttony', $lang->getLine('Allin.two'));
        $this->assertSame('Colossus of Rhodes', $lang->getLine('Allin.tre'));
        $this->assertSame('four calling birds', $lang->getLine('Allin.for'));
        $this->assertSame('Temple of Artemis', $lang->getLine('Allin.fiv'));
        $this->assertSame('envy', $lang->getLine('Allin.six'));
        $this->assertSame('Hanging Gardens of Babylon', $lang->getLine('Allin.sev'));
    }

    public function testLanguageNestedArrayDefinition(): void
    {
        $lang = new SecondMockLanguage('en');
        $lang->loadem('Nested', 'en');

        $this->assertSame('e', $lang->getLine('Nested.a.b.c.d'));
    }

    public function testLanguageKeySeparatedByDot(): void
    {
        $lang = new SecondMockLanguage('en');
        $lang->loadem('Foo', 'en');

        $this->assertSame('The fieldname field is very short.', $lang->getLine('Foo.bar.min_length1', ['field' => 'fieldname']));
        $this->assertSame('The fieldname field is very short.', $lang->getLine('Foo.baz.min_length3.short', ['field' => 'fieldname']));
    }
}
