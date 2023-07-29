<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Typography;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 *
 * @group Others
 */
final class TypographyTest extends CIUnitTestCase
{
    private Typography $typography;

    protected function setUp(): void
    {
        parent::setUp();
        $this->typography = new Typography();
    }

    public function testAutoTypographyEmptyString(): void
    {
        $this->assertSame('', $this->typography->autoTypography(''));
    }

    public function testAutoTypographyNormalString(): void
    {
        $strs = [
            'this sentence has no punctuations' => '<p>this sentence has no punctuations</p>',
            'Hello World !!, How are you?'      => '<p>Hello World !!, How are you?</p>',
        ];

        foreach ($strs as $str => $expect) {
            $this->assertSame($expect, $this->typography->autoTypography($str));
        }
    }

    public function testAutoTypographyMultipleSpaces(): void
    {
        $strs = [
            'this sentence has  a double spacing'              => '<p>this sentence has  a double spacing</p>',
            'this  sentence   has    a     weird      spacing' => '<p>this  sentence &nbsp; has &nbsp;  a &nbsp;   weird &nbsp;  &nbsp; spacing</p>',
        ];

        foreach ($strs as $str => $expect) {
            $this->assertSame($expect, $this->typography->autoTypography($str));
        }
    }

    public function testAutoTypographyLineBreaks(): void
    {
        $strs = [
            "\n"                                   => "\n\n<p>&nbsp;</p>",
            "\n\n"                                 => "\n\n<p>&nbsp;</p>",
            "\n\n\n"                               => "\n\n<p>&nbsp;</p>",
            "Line One\n"                           => "<p>Line One</p>\n\n",
            "Line One\nLine Two"                   => "<p>Line One<br>\nLine Two</p>",
            "Line One\r\n"                         => "<p>Line One</p>\n\n",
            "Line One\r\nLine Two"                 => "<p>Line One<br>\nLine Two</p>",
            "Line One\r"                           => "<p>Line One</p>\n\n",
            "Line One\rLine Two"                   => "<p>Line One<br>\nLine Two</p>",
            "Line One\n\nLine Two\n\n\nLine Three" => "<p>Line One</p>\n\n<p>Line Two</p>\n\n<p>Line Three</p>",
        ];

        foreach ($strs as $str => $expect) {
            $this->assertSame($expect, $this->typography->autoTypography($str));
        }
    }

    public function testAutoTypographyReduceLineBreaks(): void
    {
        $strs = [
            "\n"                                   => "\n\n",
            "\n\n"                                 => "\n\n",
            "\n\n\n"                               => "\n\n\n\n",
            "Line One\n"                           => "<p>Line One</p>\n\n",
            "Line One\nLine Two"                   => "<p>Line One<br>\nLine Two</p>",
            "Line One\r\n"                         => "<p>Line One</p>\n\n",
            "Line One\r\nLine Two"                 => "<p>Line One<br>\nLine Two</p>",
            "Line One\r"                           => "<p>Line One</p>\n\n",
            "Line One\rLine Two"                   => "<p>Line One<br>\nLine Two</p>",
            "Line One\n\nLine Two\n\n\nLine Three" => "<p>Line One</p>\n\n<p>Line Two</p>\n\n<p><br>\nLine Three</p>",
        ];

        foreach ($strs as $str => $expect) {
            $this->assertSame($expect, $this->typography->autoTypography($str, true));
        }
    }

    public function testAutoTypographyHTMLComment(): void
    {
        $strs = [
            '<!-- this is an HTML comment -->'                       => '<!-- this is an HTML comment -->',
            'This is not a comment.<!-- this is an HTML comment -->' => '<p>This is not a comment.<!-- this is an HTML comment --></p>',
            '<!-- this is an HTML comment -->This is not a comment.' => '<p><!-- this is an HTML comment -->This is not a comment.</p>',
        ];

        foreach ($strs as $str => $expect) {
            $this->assertSame($expect, $this->typography->autoTypography($str));
        }
    }

    public function testAutoTypographyHTMLTags(): void
    {
        $strs = [
            '<b>Hello World !!</b>, How are you?'               => '<p><b>Hello World !!</b>, How are you?</p>',
            '<p>Hello World !!, How are you?</p>'               => '<p>Hello World !!, How are you?</p>',
            '<pre>Code goes here.</pre>'                        => '<pre>Code goes here.</pre>',
            "<pre>Line One\nLine Two\n\nLine Three\n\n\n</pre>" => "<pre>Line One\nLine Two\n\nLine Three\n\n</pre>",
            'Line One</pre>'                                    => 'Line One</pre>',
        ];

        foreach ($strs as $str => $expect) {
            $this->assertSame($expect, $this->typography->autoTypography($str));
        }
    }

    public function testAutoTypographySpecialCharacters(): void
    {
        $strs = [
            '\'Text in single quotes\''      => '<p>&#8216;Text in single quotes&#8217;</p>',
            '"Text in double quotes"'        => '<p>&#8220;Text in double quotes&#8221;</p>',
            'Double dash -- becomes em-dash' => '<p>Double dash&#8212;becomes em-dash</p>',
        ];

        foreach ($strs as $str => $expect) {
            $this->assertSame($expect, $this->typography->autoTypography($str));
        }
    }

    public function testNewlinesToHTMLLineBreaksExceptWithinPRE(): void
    {
        $strs = [
            "Line One\nLine Two"            => "Line One<br>\nLine Two",
            "<pre>Line One\nLine Two</pre>" => "<pre>Line One\nLine Two</pre>",
            "<div>Line One\nLine Two</div>" => "<div>Line One<br>\nLine Two</div>",
        ];

        foreach ($strs as $str => $expect) {
            $this->assertSame($expect, $this->typography->nl2brExceptPre($str));
        }
    }
}
