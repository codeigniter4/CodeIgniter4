<?php
namespace CodeIgniter\Test;

class DOMParserTest extends CIUnitTestCase
{

	protected function setUp(): void
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

		$html     = '<div><h1>Hello</h1></div>';
		$expected = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">' . "\n"
				. '<html><body><div><h1>Hello</h1></div></body></html>';

		$this->assertEquals($expected . "\n", $dom->withString($html)->getBody());
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

	public function testSeeElementSuccess()
	{
		$dom = new DOMParser();

		$html = '<html><body><h1 id="heading">Hello World Wide Web</h1></body></html>';
		$dom->withString($html);

		$this->assertTrue($dom->seeElement('#heading'));
	}

	public function testSeeElementFail()
	{
		$dom = new DOMParser();

		$html = '<html><body><h1 id="heading">Hello World Wide Web</h1></body></html>';
		$dom->withString($html);

		$this->assertFalse($dom->seeElement('#headings'));
	}

	public function testDontSeeElementSuccess()
	{
		$dom = new DOMParser();

		$html = '<html><body><h1 id="heading">Hello World Wide Web</h1></body></html>';
		$dom->withString($html);

		$this->assertTrue($dom->dontSeeElement('#head'));
	}

	public function testDontSeeElementFail()
	{
		$dom = new DOMParser();

		$html = '<html><body><h1 id="heading">Hello World Wide Web</h1></body></html>';
		$dom->withString($html);

		$this->assertFalse($dom->dontSeeElement('#heading'));
	}

	public function testSeeLinkSuccess()
	{
		$dom = new DOMParser();

		$html = '<html><body><a href="http://example.com">Hello</a></body></html>';
		$dom->withString($html);

		$this->assertTrue($dom->seeLink('Hello'));
	}

	public function testSeeLinkFalse()
	{
		$dom = new DOMParser();

		$html = '<html><body><a href="http://example.com">Hello</a></body></html>';
		$dom->withString($html);

		$this->assertFalse($dom->seeLink('Hello World!'));
	}

	public function testSeeLinkClassSuccess()
	{
		$dom = new DOMParser();

		$html = '<html><body><a class="btn" href="http://example.com">Hello</a></body></html>';
		$dom->withString($html);

		$this->assertTrue($dom->seeLink('Hello', '.btn'));
	}

	public function testSeeLinkClassFail()
	{
		$dom = new DOMParser();

		$html = '<html><body><a class="button" href="http://example.com">Hello</a></body></html>';
		$dom->withString($html);

		$this->assertFalse($dom->seeLink('Hello', '.btn'));
	}

	public function testSeeInFieldSuccess()
	{
		$dom = new DOMParser();

		$html = '<html><body><input type="text" name="user" value="Foobar"></body></html>';
		$dom->withString($html);

		$this->assertTrue($dom->seeInField('user', 'Foobar'));
	}

	public function testSeeInFieldFail()
	{
		$dom = new DOMParser();

		$html = '<html><body><input type="text" name="user" value="Foobar"></body></html>';
		$dom->withString($html);

		$this->assertFalse($dom->seeInField('user', 'Foobars'));
	}

	public function testSeeInFieldSuccessArray()
	{
		$dom = new DOMParser();

		$html = '<html><body><input type="text" name="user[name]" value="Foobar"></body></html>';
		$dom->withString($html);

		$this->assertTrue($dom->seeInField('user[name]', 'Foobar'));
	}

	public function testSeeCheckboxIsCheckedByIDTrue()
	{
		$dom = new DOMParser();

		$html = '<html><body><input type="checkbox" name="user" id="user" value="1" checked></body></html>';
		$dom->withString($html);

		$this->assertTrue($dom->seeCheckboxIsChecked('#user'));
	}

	public function testSeeCheckboxIsCheckedByIDFail()
	{
		$dom = new DOMParser();

		$html = '<html><body><input type="checkbox" name="user" id="users" value="1" checked></body></html>';
		$dom->withString($html);

		$this->assertFalse($dom->seeCheckboxIsChecked('#user'));
	}

	public function testSeeCheckboxIsCheckedByClassTrue()
	{
		$dom = new DOMParser();

		$html = '<html><body><input type="checkbox" name="user" class="btn" value="1" checked></body></html>';
		$dom->withString($html);

		$this->assertTrue($dom->seeCheckboxIsChecked('.btn'));
	}

	public function testWithFile()
	{
		$dom = new DOMParser();

		$filename = APPPATH . 'index.html';

		$dom->withFile($filename);
		$this->assertTrue($dom->see('Directory access is forbidden.'));
	}

	public function testWithNotFile()
	{
		$dom = new DOMParser();

		$filename = APPPATH . 'bogus.html';

		$this->expectException(\InvalidArgumentException::class);
		$dom->withFile($filename);
	}

	public function testSeeAttribute()
	{
		$dom = new DOMParser();

		$path     = '[ name = user ]';
		$selector = $dom->parseSelector($path);

		$this->assertEquals(['name' => 'user'], $selector['attr']);

		$html = '<html><body><div name="user">George</div></body></html>';
		$dom->withString($html);

		$this->assertTrue($dom->see(null, '*[ name = user ]'));
		$this->assertFalse($dom->see(null, '*[ name = notthere ]'));
	}

}
