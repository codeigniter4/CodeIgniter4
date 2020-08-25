<?php
namespace CodeIgniter\Language;

use CodeIgniter\Test\Mock\MockLanguage;
use Config\Services;
use Tests\Support\Language\SecondMockLanguage;

class LanguageTest extends \CodeIgniter\Test\CIUnitTestCase
{

	public function testReturnsStringWithNoFileInMessage()
	{
		$lang = new MockLanguage('en');

		$this->assertEquals('something', $lang->getLine('something'));
	}

	//--------------------------------------------------------------------

	public function testGetLineReturnsLine()
	{
		$lang = new MockLanguage('en');

		$lang->setData('books', [
			'bookSaved'  => 'We kept the book free from the boogeyman',
			'booksSaved' => 'We saved some more',
		]);

		$this->assertEquals('We saved some more', $lang->getLine('books.booksSaved'));
	}

	//--------------------------------------------------------------------

	public function testGetLineReturnsFallbackLine()
	{
		$lang = new MockLanguage('en-US');
		$lang->setData('equivalent', [
			'touchWood'   => 'touch wood',
			'lieOfLand'   => 'lie of the land',
			'leaseOfLife' => 'a new lease of life',
			'slowcoach'   => 'slowcoach',
		], 'en');
		$lang->setData('equivalent', [
			'lieOfLand' => 'lay of the land',
			'slowcoach' => 'slowpoke',
		], 'en-US');

		$this->assertEquals(
				'lay of the land', $lang->getLine('equivalent.lieOfLand')
		);
		$this->assertEquals(
				'slowpoke', $lang->getLine('equivalent.slowcoach')
		);
		$this->assertEquals(
				'a new lease of life', $lang->getLine('equivalent.leaseOfLife')
		);
		$this->assertEquals(
				'touch wood', $lang->getLine('equivalent.touchWood')
		);
		$this->assertEquals(
				'equivalent.unknown', $lang->getLine('equivalent.unknown')
		);
	}

	//--------------------------------------------------------------------

	public function testGetLineArrayReturnsLineArray()
	{
		$lang = new MockLanguage('en');

		$lang->setData('books', [
			'booksList' => [
				'The Boogeyman',
				'We Saved',
			],
		]);

		$this->assertEquals([
			'The Boogeyman',
			'We Saved',
		], $lang->getLine('books.booksList'));
	}

	//--------------------------------------------------------------------

	public function testGetLineFormatsMessage()
	{
		// No intl extension? then we can't test this - go away....
		if (! class_exists('\MessageFormatter'))
		{
			return;
		}

		$lang = new MockLanguage('en');

		$lang->setData('books', [
			'bookCount' => '{0, number, integer} books have been saved.',
		]);

		$this->assertEquals('45 books have been saved.', $lang->getLine('books.bookCount', [91 / 2]));
	}

	//--------------------------------------------------------------------

	public function testGetLineArrayFormatsMessages()
	{
		// No intl extension? Then we can't test this - go away...
		if (! class_exists('\MessageFormatter'))
		{
			return;
		}

		$lang = new MockLanguage('en');

		$lang->setData('books', [
			'bookList' => [
				'{0, number, integer} related books.'
			],
		]);

		$this->assertEquals(['45 related books.'], $lang->getLine('books.bookList', [91 / 2]));
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/891
	 */
	public function testLangAllowsOtherLocales()
	{
		$str1 = lang('Language.languageGetLineInvalidArgumentException', [], 'en');
		$str2 = lang('Language.languageGetLineInvalidArgumentException', [], 'ru');

		$this->assertEquals('Get line must be a string or array of strings.', $str1);
		$this->assertEquals('Whatever this would be, translated', $str2);
	}

	//--------------------------------------------------------------------

	public function testLangDoesntFormat()
	{
		$lang = new MockLanguage('en');
		$lang->disableIntlSupport();

		$lang->setData('books', [
			'bookList' => [
				'{0, number, integer} related books.'
			],
		]);

		$this->assertEquals(['{0, number, integer} related books.'], $lang->getLine('books.bookList', [15]));
	}

	//--------------------------------------------------------------------

	public function testLanguageDuplicateKey()
	{
		$lang = new Language('en', false);
		$this->assertEquals('These are not the droids you are looking for', $lang->getLine('More.strongForce', []));
		$this->assertEquals('I have a very bad feeling about this', $lang->getLine('More.cannotMove', []));
		$this->assertEquals('Could not move file {0} to {1} ({2})', $lang->getLine('Files.cannotMove', []));
		$this->assertEquals('I have a very bad feeling about this', $lang->getLine('More.cannotMove', []));
	}

	//--------------------------------------------------------------------

	public function testLanguageFileLoading()
	{
		$lang = new SecondMockLanguage('en');

		$result = $lang->loadem('More', 'en');
		$this->assertTrue(in_array('More', $lang->loaded()));
		$result = $lang->loadem('More', 'en');
		$this->assertEquals(1, count($lang->loaded())); // should only be there once
	}

	//--------------------------------------------------------------------

	public function testLanguageFileLoadingReturns()
	{
		$lang = new SecondMockLanguage('en');

		$result = $lang->loadem('More', 'en', true);
		$this->assertFalse(in_array('More', $lang->loaded()));
		$this->assertEquals(3, count($result));
		$result = $lang->loadem('More', 'en');
		$this->assertTrue(in_array('More', $lang->loaded()));
		$this->assertEquals(1, count($lang->loaded()));
	}

	//--------------------------------------------------------------------

	public function testLanguageSameKeyAndFileName()
	{
		$lang = new MockLanguage('en');

		// first file data | example.message
		$lang->setData('example', ['message' => 'This is an example message']);

		// force loading data into file Example
		$this->assertEquals('This is an example message', $lang->getLine('example.message'));

		// second file data | another.example
		$lang->setData('another', ['example' => 'Another example']);

		$this->assertEquals('Another example', $lang->getLine('another.example'));
	}

	//--------------------------------------------------------------------

	public function testGetLocale()
	{
		$language = Services::language('en', false);
		$this->assertEquals('en', $language->getLocale());
	}

	//--------------------------------------------------------------------

	public function testPrioritizedLocator()
	{
		// this should load the replacement bundle of messages
		$message = lang('Core.missingExtension', [], 'en');
		$this->assertEquals('The framework needs the following extension(s) installed and loaded: {0}.', $message);
		// and we should have our new message too
		$this->assertEquals('billions and billions', lang('Core.bazillion', [], 'en'));
	}

	//--------------------------------------------------------------------

	public function MessageBundles()
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
			['Language'],
			['Log'],
			['Migrations'],
			['Number'],
			['Pager'],
			['Router'],
			['Session'],
			['Time'],
			['Validation'],
			['View'],
		];
	}

	/**
	 * There's not a whole lot that can be done with message bundles,
	 * but we can at least try loading them ... more accurate code coverage?
	 *
	 * @dataProvider MessageBundles
	 */
	public function testBundleUniqueKeys($bundle)
	{
		$language = Services::language('en', false);
		$messages = require SYSTEMPATH . 'Language/en/' . $bundle . '.php';
		$this->assertGreaterThan(0, count($messages));
	}

	//--------------------------------------------------------------------
	// Testing base locale vs variants

	public function testBaseFallbacks()
	{
		$language = Services::language('en-ZZ', false);
		// key is in both base and variant; should pick variant
		$this->assertEquals("It's made of cheese", $language->getLine('More.notaMoon'));

		// key is in base but not variant; should pick base
		$this->assertEquals('I have a very bad feeling about this', $language->getLine('More.cannotMove'));

		// key is in variant but not base; should pick variant
		$this->assertEquals('There is no try', $language->getLine('More.wisdom'));

		// key isn't in either base or variant; should return bad key
		$this->assertEquals('More.shootMe', $language->getLine('More.shootMe'));
	}

	//--------------------------------------------------------------------
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
	public function testAllTheWayFallbacks()
	{
		$language = Services::language('ab-CD', false);
		$this->assertEquals('Allin.none', $language->getLine('Allin.none'));
		$this->assertEquals('Pyramid of Giza', $language->getLine('Allin.one'));
		$this->assertEquals('gluttony', $language->getLine('Allin.two'));
		$this->assertEquals('Colossus of Rhodes', $language->getLine('Allin.tre'));
		$this->assertEquals('four calling birds', $language->getLine('Allin.for'));
		$this->assertEquals('Temple of Artemis', $language->getLine('Allin.fiv'));
		$this->assertEquals('envy', $language->getLine('Allin.six'));
		$this->assertEquals('Hanging Gardens of Babylon', $language->getLine('Allin.sev'));
	}

	public function testLanguageNestedArrayDefinition()
	{
		$lang = new SecondMockLanguage('en');
		$lang->loadem('Nested', 'en');

		$this->assertEquals('e', $lang->getLine('Nested.a.b.c.d'));
	}

	public function testLanguageKeySeparatedByDot()
	{
		$lang = new SecondMockLanguage('en');
		$lang->loadem('Foo', 'en');

		$this->assertEquals('The fieldname field is very short.', $lang->getLine('Foo.bar.min_length1', ['field' => 'fieldname']));
		$this->assertEquals('The fieldname field is very short.', $lang->getLine('Foo.baz.min_length3.short', ['field' => 'fieldname']));
	}

}
