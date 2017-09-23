<?php namespace CodeIgniter\Test;

use Tests\Support\DOM\DOMParser;

class DOMParserTest extends CIUnitTestCase
{
	public function setUp()
	{
		parent::setUp();

		if (! extension_loaded('DOM'))
		{
			$this->markTestSkipped('DOM extension not loaded.');
		}
	}

	public function testCanRoundTripHTML()
	{
		$dom = new DOMParser();

		$html = '<div><h1>Hello</h1></div>';
		$expected = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">' ."\n"
			.'<html><body><div><h1>Hello</h1></div></body></html>';

		$this->assertEquals($expected."\n", $dom->withString($html)->getBody());
	}

	public function testParseSelectorWithID()
	{
		$dom = new DOMParser();

		$selector = $dom->parseSelector('div#row');

		$this->assertEquals('div', $selector['tag']);
		$this->assertEquals('row', $selector['id']);
	}

	public function testParseSelectorWithClass()
	{
		$dom = new DOMParser();

		$selector = $dom->parseSelector('div.row');

		$this->assertEquals('div', $selector['tag']);
		$this->assertEquals('row', $selector['class']);
	}

	public function testParseSelectorWithClassMultiple()
	{
		$dom = new DOMParser();

		$selector = $dom->parseSelector('div.row.another');

		$this->assertEquals('div', $selector['tag']);
		// Only parses the first class
		$this->assertEquals('row', $selector['class']);
	}

	public function testParseSelectorWithAttribute()
	{
		$dom = new DOMParser();

		$selector = $dom->parseSelector('a[ href = http://example.com ]');

		$this->assertEquals('a', $selector['tag']);
		$this->assertEquals(['href' => 'http://example.com'], $selector['attr']);
	}


	public function testSeeText()
	{
		$dom = new DOMParser();

		$html = '<html><body><h1>Hello World</h1></body></html>';
		$dom->withString($html);

		$this->assertTrue($dom->see('Hello World'));
	}

	public function testSeeHTML()
	{
		$dom = new DOMParser();

		$html = '<html><body><h1>Hello World</h1></body></html>';
		$dom->withString($html);

		$this->assertTrue($dom->see('<h1>'));
	}

	public function testSeeFail()
	{
		$dom = new DOMParser();

		$html = '<html><body><h1>Hello World</h1></body></html>';
		$dom->withString($html);

		$this->assertFalse($dom->see('Hello Worlds'));
	}

	public function testSeeElement()
	{
		$dom = new DOMParser();

		$html = '<html><body><h1>Hello World</h1></body></html>';
		$dom->withString($html);

		$this->assertTrue($dom->see('Hello World', 'h1'));
	}

	public function testSeeElementPartialText()
	{
		$dom = new DOMParser();

		$html = '<html><body><h1>Hello World Wide Web</h1></body></html>';
		$dom->withString($html);

		$this->assertTrue($dom->see('Hello World', 'h1'));
	}

	public function testSeeElementID()
	{
		$dom = new DOMParser();

		$html = '<html><body><h1 id="heading">Hello World Wide Web</h1></body></html>';
		$dom->withString($html);

		$this->assertTrue($dom->see('Hello World', '#heading'));
	}

	public function testSeeElementIDFails()
	{
		$dom = new DOMParser();

		$html = '<html><body><h1 id="heading">Hello World Wide Web</h1></body></html>';
		$dom->withString($html);

		$this->assertFalse($dom->see('Hello Worlds', '#heading'));
	}

	public function testSeeElementIDWithTag()
	{
		$dom = new DOMParser();

		$html = '<html><body><h1 id="heading">Hello World Wide Web</h1></body></html>';
		$dom->withString($html);

		$this->assertTrue($dom->see('Hello World', 'h1#heading'));
	}

	public function testSeeElementIDWithTagFails()
	{
		$dom = new DOMParser();

		$html = '<html><body><h2 id="heading">Hello World Wide Web</h2></body></html>';
		$dom->withString($html);

		$this->assertFalse($dom->see('Hello World', 'h1#heading'));
	}

	public function testSeeElementClass()
	{
		$dom = new DOMParser();

		$html = '<html><body><h1 class="heading">Hello World Wide Web</h1></body></html>';
		$dom->withString($html);

		$this->assertTrue($dom->see('Hello World', '.heading'));
	}

	public function testSeeElementClassFail()
	{
		$dom = new DOMParser();

		$html = '<html><body><h1 class="headings">Hello World Wide Web</h1></body></html>';
		$dom->withString($html);

		$this->assertFalse($dom->see('Hello World', '.heading'));
	}

	public function testSeeElementClassWithTag()
	{
		$dom = new DOMParser();

		$html = '<html><body><h1 class="heading">Hello World Wide Web</h1></body></html>';
		$dom->withString($html);

		$this->assertTrue($dom->see('Hello World', 'h1.heading'));
	}

	public function testSeeElementClassWithTagFail()
	{
		$dom = new DOMParser();

		$html = '<html><body><h1 class="heading">Hello World Wide Web</h1></body></html>';
		$dom->withString($html);

		$this->assertFalse($dom->see('Hello World', 'h2.heading'));
	}
}
