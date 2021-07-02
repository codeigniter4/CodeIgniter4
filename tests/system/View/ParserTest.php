<?php

namespace CodeIgniter\View;

use CodeIgniter\Autoloader\FileLocator;
use CodeIgniter\Entity\Entity;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\View\Exceptions\ViewException;
use Config\Services;
use Config\View as ViewConfig;
use stdClass;

/**
 * @internal
 */
final class ParserTest extends CIUnitTestCase
{
    /**
     * @var FileLocator
     */
    private $loader;

    /**
     * @var string
     */
    private $viewsDir;

    /**
     * @var ViewConfig
     */
    private $config;

    /**
     * @var Parser
     */
    private $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader   = Services::locator();
        $this->viewsDir = __DIR__ . '/Views';
        $this->config   = new ViewConfig();
        $this->parser   = new Parser($this->config, $this->viewsDir, $this->loader);
    }

    // --------------------------------------------------------------------

    public function testSetDelimiters()
    {
        // Make sure default delimiters are there
        $this->assertSame('{', $this->parser->leftDelimiter);
        $this->assertSame('}', $this->parser->rightDelimiter);

        // Change them to square brackets
        $this->parser->setDelimiters('[', ']');

        // Make sure they changed
        $this->assertSame('[', $this->parser->leftDelimiter);
        $this->assertSame(']', $this->parser->rightDelimiter);

        // Reset them
        $this->parser->setDelimiters();

        // Make sure default delimiters are there
        $this->assertSame('{', $this->parser->leftDelimiter);
        $this->assertSame('}', $this->parser->rightDelimiter);
    }

    // --------------------------------------------------------------------

    public function testParseSimple()
    {
        $this->parser->setVar('teststring', 'Hello World');
        $this->assertSame("<h1>Hello World</h1>\n", $this->parser->render('template1'));
    }

    // --------------------------------------------------------------------

    public function testParseString()
    {
        $data = [
            'title' => 'Page Title',
            'body'  => 'Lorem ipsum dolor sit amet.',
        ];

        $template = "{title}\n{body}";

        $result = implode("\n", $data);

        $this->parser->setData($data);
        $this->assertSame($result, $this->parser->renderString($template));
    }

    // --------------------------------------------------------------------

    public function testParseStringMissingData()
    {
        $data = [
            'title' => 'Page Title',
            'body'  => 'Lorem ipsum dolor sit amet.',
        ];

        $template = "{title}\n{body}\n{name}";

        $result = implode("\n", $data) . "\n{name}";

        $this->parser->setData($data);
        $this->assertSame($result, $this->parser->renderString($template));
    }

    // --------------------------------------------------------------------

    public function testParseStringUnusedData()
    {
        $data = [
            'title' => 'Page Title',
            'body'  => 'Lorem ipsum dolor sit amet.',
            'name'  => 'Someone',
        ];

        $template = "{title}\n{body}";

        $result = "Page Title\nLorem ipsum dolor sit amet.";

        $this->parser->setData($data);
        $this->assertSame($result, $this->parser->renderString($template));
    }

    // --------------------------------------------------------------------

    public function testParseNoTemplate()
    {
        $this->assertSame('', $this->parser->renderString(''));
    }

    // --------------------------------------------------------------------

    public function testParseArraySingle()
    {
        $data = [
            'title'  => 'Super Heroes',
            'powers' => [
                [
                    'invisibility' => 'yes',
                    'flying'       => 'no',
                ],
            ],
        ];

        $template = "{title}\n{powers}{invisibility}\n{flying}{/powers}";

        $this->parser->setData($data);
        $this->assertSame("Super Heroes\nyes\nno", $this->parser->renderString($template));
    }

    public function testParseArrayMulti()
    {
        $data = [
            'powers' => [
                [
                    'invisibility' => 'yes',
                    'flying'       => 'no',
                ],
            ],
        ];

        $template = "{powers}{invisibility}\n{flying}{/powers}\nsecond:{powers} {invisibility} {flying}{ /powers}";

        $this->parser->setData($data);
        $this->assertSame("yes\nno\nsecond: yes no", $this->parser->renderString($template));
    }

    public function testParseArrayNested()
    {
        $data = [
            'title'  => 'Super Heroes',
            'powers' => [
                [
                    'invisibility' => 'yes',
                    'flying'       => [
                        [
                            'by'     => 'plane',
                            'with'   => 'broomstick',
                            'scared' => 'yes',
                        ],
                    ],
                ],
            ],
        ];

        $template = "{title}\n{powers}{invisibility}\n{flying}{by} {with}{/flying}{/powers}";

        $this->parser->setData($data);
        $this->assertSame("Super Heroes\nyes\nplane broomstick", $this->parser->renderString($template));
    }

    public function testParseArrayNestedObject()
    {
        $eagle       = new stdClass();
        $eagle->name = 'Baldy';
        $eagle->home = 'Rockies';

        $data = [
            'birds' => [
                [
                    'pop'  => $eagle,
                    'mom'  => 'Owl',
                    'kids' => [
                        'Tom',
                        'Dick',
                        'Harry',
                    ],
                    'home' => opendir('.'),
                ],
            ],
        ];

        $template = '{birds}{mom} and {pop} work at {home}{/birds}';

        $this->parser->setData($data);
        $this->assertSame('Owl and Class: stdClass work at Resource', $this->parser->renderString($template));
    }

    // --------------------------------------------------------------------

    public function testParseLoop()
    {
        $data = [
            'title'  => 'Super Heroes',
            'powers' => [
                ['name' => 'Tom'],
                ['name' => 'Dick'],
                ['name' => 'Henry'],
            ],
        ];

        $template = "{title}\n{powers}{name} {/powers}";

        $this->parser->setData($data);
        $this->assertSame("Super Heroes\nTom Dick Henry ", $this->parser->renderString($template));
    }

    public function testParseLoopObjectProperties()
    {
        $obj1 = new stdClass();
        $obj2 = new stdClass();
        $obj3 = new stdClass();

        $obj1->name = 'Tom';
        $obj2->name = 'Dick';
        $obj3->name = 'Henry';

        $data = [
            'title'  => 'Super Heroes',
            'powers' => [
                $obj1,
                $obj2,
                $obj3,
            ],
        ];

        $template = "{title}\n{powers}{name} {/powers}";

        $this->parser->setData($data, 'html');
        $this->assertSame("Super Heroes\nTom Dick Henry ", $this->parser->renderString($template));
    }

    // --------------------------------------------------------------------

    public function testParseLoopEntityProperties()
    {
        $power             = new class() extends Entity {
            public $foo    = 'bar';
            protected $bar = 'baz';

            public function toArray(bool $onlyChanged = false, bool $cast = true, bool $recursive = false): array
            {
                return [
                    'foo'     => $this->foo,
                    'bar'     => $this->bar,
                    'bobbles' => [
                        ['name' => 'first'],
                        ['name' => 'second'],
                    ],
                ];
            }
        };

        $data = [
            'title'  => 'Super Heroes',
            'powers' => [$power],
        ];

        $template = "{title}\n{powers} {foo} {bar} {bobbles}{name} {/bobbles}{/powers}";

        $this->parser->setData($data);
        $this->assertSame("Super Heroes\n bar baz first second ", $this->parser->renderString($template));
    }

    public function testParseLoopEntityObjectProperties()
    {
        $power                    = new class() extends Entity {
            protected $attributes = [
                'foo'     => 'bar',
                'bar'     => 'baz',
                'obj1'    => null,
                'obj2'    => null,
                'bobbles' => [],
            ];

            public function __construct()
            {
                $this->obj1       = new stdClass();
                $this->obj2       = new stdClass();
                $this->obj1->name = 'first';
                $this->obj2->name = 'second';
                $this->bobbles    = [
                    $this->obj1,
                    $this->obj2,
                ];
            }
        };

        $data = [
            'title'  => 'Super Heroes',
            'powers' => [
                $power,
            ],
        ];

        $template = "{title}\n{powers} {foo} {bar} {bobbles}{name} {/bobbles}{/powers}";

        $this->parser->setData($data, 'html');
        $this->assertSame("Super Heroes\n bar baz first second ", $this->parser->renderString($template));
    }

    // --------------------------------------------------------------------

    public function testMismatchedVarPair()
    {
        $data = [
            'title'  => 'Super Heroes',
            'powers' => [
                [
                    'invisibility' => 'yes',
                    'flying'       => 'no',
                ],
            ],
        ];

        $template = "{title}\n{powers}{invisibility}\n{flying}";
        $result   = "Super Heroes\n{powers}{invisibility}\n{flying}";

        $this->parser->setData($data);
        $this->assertSame($result, $this->parser->renderString($template));
    }

    public function escValueTypes()
    {
        return [
            'scalar'     => [42],
            'string'     => ['George'],
            'scalarlist' => [
                [
                    1,
                    2,
                    17,
                    -4,
                ],
            ],
            'stringlist' => [
                [
                    'George',
                    'Paul',
                    'John',
                    'Ringo',
                ],
            ],
            'associative' => [
                [
                    'name' => 'George',
                    'role' => 'guitar',
                ],
            ],
            'compound' => [
                [
                    'name'    => 'George',
                    'address' => [
                        'line1'  => '123 Some St',
                        'planet' => 'Naboo',
                    ],
                ],
            ],
            'pseudo' => [
                [
                    'name'   => 'George',
                    'emails' => [
                        [
                            'email' => 'me@here.com',
                            'type'  => 'home',
                        ],
                        [
                            'email' => 'me@there.com',
                            'type'  => 'work',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider escValueTypes
     */
    public function testEscHandling($value, $expected = null)
    {
        if ($expected === null) {
            $expected = $value;
        }
        $this->assertSame($expected, \esc($value));
    }

    //------------------------------------------------------------------------

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/3726
     */
    public function testParseSimilarVariableNames()
    {
        $template = '{foo} {foo_bar}';

        $this->parser->setData(['foo' => 'bar', 'foo_bar' => 'foo-bar'], 'raw');
        $this->assertSame('bar foo-bar', $this->parser->renderString($template));
    }

    public function testParsePairSimilarVariableNames()
    {
        $data = [
            'title'  => '<script>Heroes</script>',
            'powers' => [
                [
                    'link'        => "<a href='test'>Link</a>",
                    'link_second' => "<a href='test2'>Link second</a>",
                ],
            ],
        ];

        $template = '{title} {powers}{link} {link_second}{/powers}';
        $this->parser->setData($data);
        $this->assertSame('&lt;script&gt;Heroes&lt;/script&gt; &lt;a href=&#039;test&#039;&gt;Link&lt;/a&gt; &lt;a href=&#039;test2&#039;&gt;Link second&lt;/a&gt;', $this->parser->renderString($template));
    }

    //------------------------------------------------------------------------

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/788
     */
    public function testEscapingRespectsSetDataRaw()
    {
        $template = '{ foo }';

        $this->parser->setData(['foo' => '<script>'], 'raw');
        $this->assertSame('<script>', $this->parser->renderString($template));
    }

    public function testEscapingSetDataWithOtherContext()
    {
        $template = '{ foo }';

        $this->parser->setData(['foo' => 'http://foo.com'], 'url');
        $this->assertSame('http%3A%2F%2Ffoo.com', $this->parser->renderString($template));
    }

    public function testNoEscapingSetData()
    {
        $template = '{ foo | noescape}';

        $this->parser->setData(['foo' => 'http://foo.com'], 'unknown');
        $this->assertSame('http://foo.com', $this->parser->renderString($template));
    }

    public function testAutoEscaping()
    {
        $this->parser->setData(['foo' => 'http://foo.com'], 'unknown');

        $this->assertSame('html', $this->parser->shouldAddEscaping('{ foo | this | that }'));
    }

    public function testAutoEscapingNot()
    {
        $this->parser->setData(['foo' => 'http://foo.com'], 'unknown');
        $this->assertFalse($this->parser->shouldAddEscaping('{ foo | noescape }'));
    }

    //--------------------------------------------------------------------

    public function testFilterWithNoArgument()
    {
        $data = [
            'that_thing' => '<script>alert("ci4")</script>',
        ];

        $template = '{ that_thing|esc }';

        $this->parser->setData($data);
        $this->assertSame('&lt;script&gt;alert(&quot;ci4&quot;)&lt;/script&gt;', $this->parser->renderString($template));
    }

    public function testFilterWithArgument()
    {
        $date = time();

        $data = [
            'my_date' => $date,
        ];

        $template = '{ my_date| date(Y-m-d ) }';

        $this->parser->setData($data);
        $this->assertSame(date('Y-m-d', $date), $this->parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testParserEscapesDataDefaultsToHTML()
    {
        $data = [
            'title'  => '<script>Heroes</script>',
            'powers' => [
                ['link' => "<a href='test'>Link</a>"],
            ],
        ];

        $template = '{title} {powers}{link}{/powers}';
        $this->parser->setData($data);
        $this->assertSame('&lt;script&gt;Heroes&lt;/script&gt; &lt;a href=&#039;test&#039;&gt;Link&lt;/a&gt;', $this->parser->renderString($template));
    }

    public function testParserNoEscape()
    {
        $data = [
            'title' => '<script>Heroes</script>',
        ];

        $template = '{! title!}';
        $this->parser->setData($data);
        $this->assertSame('<script>Heroes</script>', $this->parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testIgnoresComments()
    {
        $data = [
            'title'  => 'Super Heroes',
            'powers' => [
                [
                    'invisibility' => 'yes',
                    'flying'       => 'no',
                ],
            ],
        ];

        $template = "{# Comments #}{title}\n{powers}{invisibility}\n{flying}";
        $result   = "Super Heroes\n{powers}{invisibility}\n{flying}";

        $this->parser->setData($data);
        $this->assertSame($result, $this->parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testNoParse()
    {
        $data = [
            'title'  => 'Super Heroes',
            'powers' => [
                [
                    'invisibility' => 'yes',
                    'flying'       => 'no',
                ],
            ],
        ];

        $template = "{noparse}{title}\n{powers}{invisibility}\n{flying}{/noparse}";
        $result   = "{title}\n{powers}{invisibility}\n{flying}";

        $this->parser->setData($data);
        $this->assertSame($result, $this->parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testIfConditionalTrue()
    {
        $data = [
            'doit'     => true,
            'dontdoit' => false,
        ];

        $template = '{if $doit}Howdy{endif}{ if $dontdoit === false}Welcome{ endif }';
        $this->parser->setData($data);

        $this->assertSame('HowdyWelcome', $this->parser->renderString($template));
    }

    public function testElseConditionalFalse()
    {
        $data = [
            'doit' => true,
        ];

        $template = '{if $doit}Howdy{else}Welcome{ endif }';
        $this->parser->setData($data);

        $this->assertSame('Howdy', $this->parser->renderString($template));
    }

    public function testElseConditionalTrue()
    {
        $data = [
            'doit' => false,
        ];

        $template = '{if $doit}Howdy{else}Welcome{ endif }';
        $this->parser->setData($data);

        $this->assertSame('Welcome', $this->parser->renderString($template));
    }

    public function testElseifConditionalTrue()
    {
        $data = [
            'doit'     => false,
            'dontdoit' => true,
        ];

        $template = '{if $doit}Howdy{elseif $dontdoit}Welcome{ endif }';
        $this->parser->setData($data);

        $this->assertSame('Welcome', $this->parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testConditionalBadSyntax()
    {
        $this->expectException(ViewException::class);
        $data = [
            'doit'     => true,
            'dontdoit' => false,
        ];

        // the template is purposefully malformed
        $template = '{if doit}Howdy{elseif doit}Welcome{ endif )}';

        $this->parser->setData($data);
        $this->assertSame('HowdyWelcome', $this->parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testWontParsePHP()
    {
        $template = "<?php echo 'Foo' ?> - <?= 'Bar' ?>";
        $this->assertSame('&lt;?php echo \'Foo\' ?&gt; - &lt;?= \'Bar\' ?&gt;', $this->parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testParseHandlesSpaces()
    {
        $data = [
            'title' => 'Page Title',
            'body'  => 'Lorem ipsum dolor sit amet.',
        ];

        $template = "{ title}\n{ body }";

        $result = implode("\n", $data);

        $this->parser->setData($data);
        $this->assertSame($result, $this->parser->renderString($template));
    }

    // --------------------------------------------------------------------

    public function testParseRuns()
    {
        $data = [
            'title' => 'Page Title',
            'body'  => 'Lorem ipsum dolor sit amet.',
        ];

        $template = "{ title}\n{ body }";

        $result = implode("\n", $data);

        $this->parser->setData($data);
        $this->assertSame($result, $this->parser->renderString($template));
    }

    //--------------------------------------------------------------------

    /**
     * @group parserplugins
     */
    public function testCanAddAndRemovePlugins()
    {
        $this->parser->addPlugin('first', static function ($str) {
            return $str;
        });

        $setParsers = $this->getPrivateProperty($this->parser, 'plugins');

        $this->assertArrayHasKey('first', $setParsers);

        $this->parser->removePlugin('first');

        $setParsers = $this->getPrivateProperty($this->parser, 'plugins');

        $this->assertArrayNotHasKey('first', $setParsers);
    }

    //--------------------------------------------------------------------

    /**
     * @group parserplugins
     */
    public function testParserPluginNoMatches()
    {
        $template = 'hit:it';

        $this->assertSame('hit:it', $this->parser->renderString($template));
    }

    //--------------------------------------------------------------------

    /**
     * @group parserplugins
     */
    public function testParserPluginNoParams()
    {
        $this->parser->addPlugin('hit:it', static function ($str) {
            return str_replace('here', 'Hip to the Hop', $str);
        }, true);

        $template = '{+ hit:it +} stuff here {+ /hit:it +}';

        $this->assertSame(' stuff Hip to the Hop ', $this->parser->renderString($template));
    }

    //--------------------------------------------------------------------

    /**
     * @group parserplugins
     */
    public function testParserPluginClosure()
    {
        $config                   = $this->config;
        $config->plugins['hello'] = static function (array $params = []) {
            return 'Hello, ' . trim($params[0]);
        };

        $this->parser = new Parser($config, $this->viewsDir, $this->loader);

        $template = '{+ hello world +}';

        $this->assertSame('Hello, world', $this->parser->renderString($template));
    }

    //--------------------------------------------------------------------

    /**
     * @group parserplugins
     */
    public function testParserPluginParams()
    {
        $this->parser->addPlugin('growth', static function ($str, array $params) {
            $step = $params['step'] ?? 1;
            $count = $params['count'] ?? 2;

            $out = '';

            for ($i = 1; $i <= $count; $i++) {
                $out .= ' ' . $i * $step;
            }

            return $out;
        }, true);

        $template = '{+ growth step=2 count=4 +}  {+ /growth +}';

        $this->assertSame(' 2 4 6 8', $this->parser->renderString($template));
    }

    //--------------------------------------------------------------------

    /**
     * @group parserplugins
     */
    public function testParserSingleTag()
    {
        $this->parser->addPlugin('hit:it', static function () {
            return 'Hip to the Hop';
        }, false);

        $template = '{+ hit:it +}';

        $this->assertSame('Hip to the Hop', $this->parser->renderString($template));
    }

    /**
     * @group parserplugins
     */
    public function testParserSingleTagWithParams()
    {
        $this->parser->addPlugin('hit:it', static function (array $params = []) {
            return "{$params['first']} to the {$params['last']}";
        }, false);

        $template = '{+ hit:it first=foo last=bar +}';

        $this->assertSame('foo to the bar', $this->parser->renderString($template));
    }

    /**
     * @group parserplugins
     */
    public function testParserSingleTagWithSingleParams()
    {
        $this->parser->addPlugin('hit:it', static function (array $params = []) {
            return "{$params[0]} to the {$params[1]}";
        }, false);

        $template = '{+ hit:it foo bar +}';

        $this->assertSame('foo to the bar', $this->parser->renderString($template));
    }

    /**
     * @group parserplugins
     */
    public function testParserSingleTagWithQuotedParams()
    {
        $this->parser->addPlugin('count', static function (array $params = []) {
            $out = '';

            foreach ($params as $index => $param) {
                $out .= "{$index}. {$param} ";
            }

            return $out;
        }, false);

        $template = '{+ count "foo bar" baz "foo bar" +}';

        $this->assertSame('0. foo bar 1. baz 2. foo bar ', $this->parser->renderString($template));
    }

    /**
     * @group parserplugins
     */
    public function testParserSingleTagWithNamedParams()
    {
        $this->parser->addPlugin('read_params', static function (array $params = []) {
            $out = '';

            foreach ($params as $index => $param) {
                $out .= "{$index}: {$param}. ";
            }

            return $out;
        }, false);

        $template = '{+ read_params title="Hello world" page=5 email=test@test.net +}';

        $this->assertSame('title: Hello world. page: 5. email: test@test.net. ', $this->parser->renderString($template));
    }

    //--------------------------------------------------------------------

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/705
     */
    public function testParseLoopWithDollarSign()
    {
        $data = [
            'books' => [
                ['price' => '12.50'],
            ],
        ];

        $template = '{books}<p>Price $: {price}</p>{/books}';

        $this->parser->setData($data);
        $this->assertSame('<p>Price $: 12.50</p>', $this->parser->renderString($template));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/3824
     */
    public function testParseLoopWithHashSign(): void
    {
        $data = [
            'books' => [
                ['price' => '12.50'],
            ],
        ];

        $template = '{books}<p style="color: #f00">Price $: {price}</p>{/books}';

        $this->parser->setData($data);
        $this->assertSame('<p style="color: #f00">Price $: 12.50</p>', $this->parser->renderString($template));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4367
     */
    public function testParseLoopWithHashInPrecession(): void
    {
        $data = [
            'heading' => 'My Title',
            'entries' => [
                [
                    'title' => 'Subtitle',
                    'body'  => 'Lorem ipsum',
                ],
            ],
        ];

        $template = <<<'EOF'
            <h3>#{heading}</h3>
            {entries}
            	<h5>#{title}</h5>
            	<p>{body}</p>
            {/entries}
            EOF;

        $expected = <<<'EOF'
            <h3>#My Title</h3>

            	<h5>#Subtitle</h5>
            	<p>Lorem ipsum</p>

            EOF;

        $this->parser->setData($data);
        $this->assertSame($expected, $this->parser->renderString($template));
    }

    //--------------------------------------------------------------------

    public function testCachedRender()
    {
        $this->parser->setVar('teststring', 'Hello World');

        $expected = "<h1>Hello World</h1>\n";
        $this->assertSame($expected, $this->parser->render('template1', ['cache' => 10, 'cache_name' => 'HelloWorld']));
        // this second renderings should go thru the cache
        $this->assertSame($expected, $this->parser->render('template1', ['cache' => 10, 'cache_name' => 'HelloWorld']));
    }

    //--------------------------------------------------------------------

    public function testRenderFindsView()
    {
        $this->parser->setData(['testString' => 'Hello World']);
        $this->assertSame("<h1>Hello World</h1>\n", $this->parser->render('Simpler'));
    }

    public function testRenderCannotFindView()
    {
        $this->expectException(ViewException::class);
        $this->expectExceptionMessageMatches('!\AInvalid file: (?:.+)View(?:/|\\\\)Views(?:/|\\\\)Simplest\.php\z!');

        $this->parser->setData(['testString' => 'Hello World']);
        $this->parser->render('Simplest');
    }

    //--------------------------------------------------------------------

    public function testRenderSavingData()
    {
        $expected = "<h1>Hello World</h1>\n";

        $this->parser->setData(['testString' => 'Hello World']);
        $this->assertSame($expected, $this->parser->render('Simpler', [], false));
        $this->assertArrayNotHasKey('testString', $this->parser->getData());

        $this->parser->setData(['testString' => 'Hello World']);
        $this->assertSame($expected, $this->parser->render('Simpler', [], true));
        $this->assertArrayHasKey('testString', $this->parser->getData());
    }

    public function testRenderStringSavingData()
    {
        $expected = '<h1>Hello World</h1>';
        $pattern  = '<h1>{testString}</h1>';

        $this->parser->setData(['testString' => 'Hello World']);
        $this->assertSame($expected, $this->parser->renderString($pattern, [], false));
        $this->assertArrayNotHasKey('testString', $this->parser->getData());
        //last set data is not saved
        $this->parser->setData(['testString' => 'Hello World']);
        $this->assertSame($expected, $this->parser->renderString($pattern, [], true));
        $this->assertArrayHasKey('testString', $this->parser->getData());
    }

    public function testRenderFindsOtherView()
    {
        $this->parser->setData(['testString' => 'Hello World']);
        $expected = '<h1>Hello World</h1>';
        $this->assertSame($expected, $this->parser->render('Simpler.html'));
    }
}
