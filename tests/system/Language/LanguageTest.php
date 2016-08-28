<?php namespace CodeIgniter\Language;

class LanguageTest extends \CIUnitTestCase
{
	public function testThrowsWithNoFileInMessage()
	{
	    $lang = new MockLanguage('en');

		$this->setExpectedException('\InvalidArgumentException');

		$lang->getLine('something');
	}

	//--------------------------------------------------------------------

	public function testGetLineReturnsLine()
	{
	    $lang = new MockLanguage('en');

		$lang->setData([
			'bookSaved' => 'We kept the book free from the boogeyman',
			'booksSaved' => 'We saved some more'
		]);

		$this->assertEquals('We saved some more', $lang->getLine('books.booksSaved'));
	}

	//--------------------------------------------------------------------

	public function testGetLineFormatsMessage()
	{
		// No intl extension? then we can't test this - go away....
		if (! class_exists('\MessageFormatter')) return;

	    $lang = new MockLanguage('en');

		$lang->setData([
			'books' => '{0, number, integer} books have been saved.'
		]);

		$this->assertEquals('45 books have been saved.', $lang->getLine('books.books', [91/2]));
	}

	//--------------------------------------------------------------------

}
