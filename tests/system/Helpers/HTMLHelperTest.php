<?php namespace CodeIgniter\HTTP;

final class HTMLHelperTest extends \CIUnitTestCase
{

    public function setUp()
    {
        //URL is needed by the HTML Helper.
        helper('url');
        helper('html');
    }

    //--------------------------------------------------------------------
    
    public function testUL()
    {
        $expected = <<<EOH
<ul>
  <li>foo</li>
  <li>bar</li>
</ul>

EOH;

        $expected = ltrim($expected);
        $list     = array('foo', 'bar');

        $this->assertEquals(ltrim($expected), ul($list));

        $expected = <<<EOH
<ul class="test">
  <li>foo</li>
  <li>bar</li>
</ul>

EOH;

        $expected = ltrim($expected);
        $this->assertEquals($expected, ul($list, 'class="test"'));
    }

    // ------------------------------------------------------------------------

    public function testIMG()
    {
        //TODO: Mock baseURL and siteURL.
        $this->assertEquals
        (
            '<img src="http://site.com/images/picture.jpg" alt="" />', 
            img('http://site.com/images/picture.jpg')
        );
    }

    // ------------------------------------------------------------------------

    public function testScriptTag()
    {
        $this->assertEquals
        (
            '<script src="http://site.com/js/mystyles.js" type="text/javascript"></script>',
            script_tag('http://site.com/js/mystyles.js')
        );
    }

    // ------------------------------------------------------------------------

    public function testLinkTag()
    {
        $this->assertEquals
        (
            '<link href="http://site.com/css/mystyles.css" rel="stylesheet" type="text/css" />',
            link_tag('http://site.com/css/mystyles.css')
        );
    }

    // ------------------------------------------------------------------------

    public function testDocType()
    {
        $this->assertEquals
        (
            '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
            doctype('html4-strict')
        );
    }

}