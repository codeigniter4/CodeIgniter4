<?php

namespace CodeIgniter\View;

use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;
use Config\View;

/**
 * @internal
 */
final class ParserFilterTest extends CIUnitTestCase
{
    protected $loader;
    protected $viewsDir;
    protected $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader   = Services::locator();
        $this->viewsDir = __DIR__ . '/Views';
        $this->config   = new View();
    }

    //--------------------------------------------------------------------

    public function testAbs()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $data = [
            'value1' => -5,
            'value2' => 5,
        ];

        $template = '{ value1|abs }{ value2|abs }';

        $parser->setData($data);
        $this->assertSame('55', $parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testCapitalize()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $data = [
            'value1' => 'wonder',
            'value2' => 'TWInS',
        ];

        $template = '{ value1|capitalize } { value2|capitalize }';

        $parser->setData($data);
        $this->assertSame('Wonder Twins', $parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testDate()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $todayDash      = date('Y-m-d');
        $todayDot       = date('Y.m.d');
        $todaySpace     = date('Y m d');
        $todayColon     = date('Y:m:d');
        $todaySlash     = date('Y/m/d');
        $todayBackslash = date('Y\\\m\\\d');

        $data = [
            'value1' => time(),
            'value2' => date('Y-m-d H:i:s'),
        ];

        $template = '{ value1|date(Y-m-d) } { value2|date(Y-m-d) } { value1|date(Y.m.d) } { value1|date(Y m d) } { value1|date(Y:m:d) } { value1|date(Y/m/d) } { value1|date(Y\\\m\\\d) }';

        $parser->setData($data);
        $this->assertSame("{$todayDash} {$todayDash} {$todayDot} {$todaySpace} {$todayColon} {$todaySlash} {$todayBackslash}", $parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testDateModify()
    {
        $parser    = new Parser($this->config, $this->viewsDir, $this->loader);
        $tommorrow = date('Y-m-d', strtotime('+1 day'));

        $data = [
            'value1' => time(),
            'value2' => date('Y-m-d H:i:s'),
        ];

        $template = '{ value1|date_modify(+1 day)|date(Y-m-d) } { value2|date_modify(+1 day)|date(Y-m-d) }';

        $parser->setData($data);
        $this->assertSame("{$tommorrow} {$tommorrow}", $parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testDefault()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $data = [
            'value1' => null,
            'value2' => 0,
            'value3' => 'test',
        ];

        $template = '{ value1|default(foo) } { value2|default(bar) } { value3|default(baz) }';

        $parser->setData($data);
        $this->assertSame('foo bar test', $parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testEsc()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $value1 = esc('<script>');
        $value2 = esc('<script>', 'js');

        $data = [
            'value1' => '<script>',
        ];

        $template = '{ value1|esc(html) } { value1|esc(js) }';

        $parser->setData($data);
        $this->assertSame("{$value1} {$value2}", $parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testExcerpt()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $data = [
            'value1' => 'The quick red fox jumped over the lazy brown dog',
        ];

        $template = '{ value1|excerpt(jumped, 10) }';

        $parser->setData($data);
        $this->assertSame('... red fox jumped over ...', $parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testHighlight()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $data = [
            'value1' => 'The quick red fox jumped over the lazy brown dog',
        ];

        $template = '{ value1|highlight(jumped over) }';

        $parser->setData($data);
        $this->assertSame('The quick red fox <mark>jumped over</mark> the lazy brown dog', $parser->renderString($template));
    }

    public function testHighlightCode()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $data = [
            'value1' => 'Sincerely',
        ];
        $parser->setData($data);

        $template = '{ value1|highlight_code }';
        $expected = <<<'EOF'
            <code><span style="color: #000000">
            <span style="color: #0000BB">Sincerely&nbsp;</span>
            </span>
            </code>
            EOF;
        $this->assertSame($expected, $parser->renderString($template));
    }

    public function testProse()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $data = [
            'value1' => 'Sincerely\nMe',
        ];
        $parser->setData($data);

        $template = '{ value1|prose }';
        $expected = '<p>Sincerely\nMe</p>';
        $this->assertSame($expected, $parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testLimitChars()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $data = [
            'value1' => 'The quick red fox jumped over the lazy brown dog',
        ];

        $template = '{ value1|limit_chars(10) }';

        $parser->setData($data);
        $this->assertSame('The quick&#8230;', $parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testLimitWords()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $data = [
            'value1' => 'The quick red fox jumped over the lazy brown dog',
        ];

        $template = '{ value1|limit_words(4) }';

        $parser->setData($data);
        $this->assertSame('The quick red fox&#8230;', $parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testLower()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $data = [
            'value1' => 'SOMETHING',
        ];

        $template = '{ value1|lower }';

        $parser->setData($data);
        $this->assertSame('something', $parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testNL2BR()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $data = [
            'value1' => "first\nsecond",
        ];

        $template = '{ value1|nl2br }';

        $parser->setData($data);
        $this->assertSame("first<br />\nsecond", $parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testNumberFormat()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $data = [
            'value1' => 1098.3455433553,
        ];

        $template = '{ value1|number_format(2) }';

        $parser->setData($data);
        $this->assertSame('1,098.35', $parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testRound()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $data = [
            'value1' => 5.55,
        ];

        $template = '{ value1|round(1) } { value1|round(1, common) } { value1|round(ceil) } { value1|round(floor) } { value1|round(unknown) }';

        $parser->setData($data);
        $this->assertSame('5.6 5.6 6 5 5.55', $parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testStripTags()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $data = [
            'value1' => '<p><b>Middle</b></p>',
        ];

        $template = '{ value1|strip_tags } { value1|strip_tags(<b>) }';

        $parser->setData($data);
        $this->assertSame('Middle <b>Middle</b>', $parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testTitle()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $data = [
            'value1' => 'THOUGH SHE BE LITTLE',
        ];

        $template = '{ value1|title }';

        $parser->setData($data);
        $this->assertSame('Though She Be Little', $parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testUpper()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $data = [
            'value1' => 'Though She Be Little',
        ];

        $template = '{ value1|upper }';

        $parser->setData($data);
        $this->assertSame('THOUGH SHE BE LITTLE', $parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testLocalNumberBase()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $data = [
            'mynum' => 1234567.891234567890000,
        ];

        $template = '{ mynum|local_number }';

        $parser->setData($data);
        $this->assertSame('1,234,567.8912', $parser->renderString($template));
    }

    public function testLocalNumberPrecision()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $data = [
            'mynum' => 1234567.891234567890000,
        ];

        $template = '{ mynum|local_number(decimal,2) }';

        $parser->setData($data);
        $this->assertSame('1,234,567.89', $parser->renderString($template));
    }

    public function testLocalNumberType()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $data = [
            'mynum' => 1234567.891234567890000,
        ];

        $template = '{ mynum|local_number(spellout) }';

        $parser->setData($data);
        $this->assertSame('one million two hundred thirty-four thousand five hundred sixty-seven point eight nine one two three four six', $parser->renderString($template));
    }

    public function testLocalNumberLocale()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $data = [
            'mynum' => 1234567.891234567890000,
        ];

        $template = '{ mynum|local_number(decimal,4,de_DE) }';

        $parser->setData($data);
        $this->assertSame('1.234.567,8912', $parser->renderString($template));
    }

    public function testLocalCurrency()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $data = [
            'mynum' => 1234567.891234567890000,
        ];

        $template = '{ mynum|local_currency(EUR,de_DE,2) }';

        $parser->setData($data);
        $this->assertSame("1.234.567,89\u{a0}€", $parser->renderString($template));
    }

    public function testParsePairWithAbs()
    {
        $parser = new Parser($this->config, $this->viewsDir, $this->loader);

        $data = [
            'value1' => -1,
            'value2' => 1,
            'single' => [
                [
                    'svalue1' => -2,
                    'svalue2' => 2,
                ],
            ],
            'loop' => [
                [
                    'lvalue' => -3,
                ],
                [
                    'lvalue' => 3,
                ],
            ],
            'nested' => [
                [
                    'nvalue1' => -4,
                    'nvalue2' => 4,
                    'nsingle' => [
                        [
                            'nsvalue1' => -5,
                            'nsvalue2' => 5,
                        ],
                    ],
                    'nsloop' => [
                        [
                            'nlvalue' => -6,
                        ],
                        [
                            'nlvalue' => 6,
                        ],
                    ],
                ],
            ],
        ];

        $template = '{ value1|abs }{ value2|abs }'
            . '{single}{ svalue1|abs }{ svalue2|abs }{/single}'
            . '{loop}{ lvalue|abs }{/loop}'
            . '{nested}'
            . '{ nvalue1|abs }{ nvalue2|abs }'
            . '{nsingle}{ nsvalue1|abs }{ nsvalue2|abs }{/nsingle}'
            . '{nsloop}{ nlvalue|abs }{/nsloop}'
            . '{/nested}';

        $parser->setData($data);
        $this->assertSame('112233445566', $parser->renderString($template));
    }
}
