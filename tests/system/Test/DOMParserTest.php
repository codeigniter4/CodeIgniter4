<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

/**
 * @internal
 *
 * @group Others
 */
final class DOMParserTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('DOM')) {
            $this->markTestSkipped('DOM extension not loaded.');
        }
    }

    public function testCanRoundTripHTML()
    {
        $dom = new DOMParser();

        $html     = '<div><h1>Hello</h1></div>';
        $expected = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">' . "\n"
                . '<html><body><div><h1>Hello</h1></div></body></html>';

        $this->assertSame($expected . "\n", $dom->withString($html)->getBody());
    }

    public function testParseSelectorWithID()
    {
        $dom = new DOMParser();

        $selector = $dom->parseSelector('div#row');

        $this->assertSame('div', $selector['tag']);
        $this->assertSame('row', $selector['id']);
    }

    public function testParseSelectorWithClass()
    {
        $dom = new DOMParser();

        $selector = $dom->parseSelector('div.row');

        $this->assertSame('div', $selector['tag']);
        $this->assertSame('row', $selector['class']);
    }

    public function testParseSelectorWithClassMultiple()
    {
        $dom = new DOMParser();

        $selector = $dom->parseSelector('div.row.another');

        $this->assertSame('div', $selector['tag']);
        // Only parses the first class
        $this->assertSame('row', $selector['class']);
    }

    public function testParseSelectorWithAttribute()
    {
        $dom = new DOMParser();

        $selector = $dom->parseSelector('a[ href = http://example.com ]');

        $this->assertSame('a', $selector['tag']);
        $this->assertSame(['href' => 'http://example.com'], $selector['attr']);
    }

    public function provideText()
    {
        return [
            'en' => ['Hello World'],
            'sv' => ['Hej, världen'],
            'ja' => ['こんにちは、世界'],
        ];
    }

    /**
     * @dataProvider provideText
     *
     * @param mixed $text
     */
    public function testSeeText($text)
    {
        $dom = new DOMParser();

        $html = '<html><body><h1>' . $text . '</h1></body></html>';
        $dom->withString($html);

        $this->assertTrue($dom->see($text));
    }

    public function testSeeHTML()
    {
        $dom = new DOMParser();

        $html = '<html><body><h1>Hello World</h1></body></html>';
        $dom->withString($html);

        $this->assertTrue($dom->see('<h1>'));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/3984
     */
    public function testSeeHTMLOutsideBodyTag()
    {
        $dom = new DOMParser();

        $html = '<html><head><title>My Title</title></head><body><h1>Hello World</h1></body></html>';
        $dom->withString($html);

        $this->assertTrue($dom->see('My Title', 'title'));
    }

    public function testSeeFail()
    {
        $dom = new DOMParser();

        $html = '<html><body><h1>Hello World</h1></body></html>';
        $dom->withString($html);

        $this->assertFalse($dom->see('Hello Worlds'));
    }

    /**
     * @dataProvider provideText
     *
     * @param mixed $text
     */
    public function testSeeElement($text)
    {
        $dom = new DOMParser();

        $html = '<html><body><h1> ' . $text . '</h1></body></html>';
        $dom->withString($html);

        $this->assertTrue($dom->see($text, 'h1'));
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

        $this->expectException('InvalidArgumentException');
        $dom->withFile($filename);
    }

    public function testSeeAttribute()
    {
        $dom = new DOMParser();

        $path     = '[ name = user ]';
        $selector = $dom->parseSelector($path);

        $this->assertSame(['name' => 'user'], $selector['attr']);

        $html = '<html><body><div name="user">George</div></body></html>';
        $dom->withString($html);

        $this->assertTrue($dom->see(null, '*[ name = user ]'));
        $this->assertFalse($dom->see(null, '*[ name = notthere ]'));
    }
}
