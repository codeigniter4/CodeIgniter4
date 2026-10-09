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

namespace CodeIgniter\View;

use CodeIgniter\Autoloader\FileLocatorInterface;
use CodeIgniter\Entity\Entity;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\View\Exceptions\ViewException;
use Config\Services;
use Config\View as ViewConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use stdClass;

/**
 * @internal
 */
#[Group('Others')]
final class ParserTest extends CIUnitTestCase
{
    private FileLocatorInterface $loader;
    private string $viewsDir;
    private ViewConfig $config;
    private Parser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader   = service('locator');
        $this->viewsDir = __DIR__ . '/Views';
        $this->config   = new ViewConfig();
        $this->parser   = new Parser($this->config, $this->viewsDir, $this->loader);
    }

    public function testSetDelimiters(): void
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

    public function testParseSimple(): void
    {
        $this->parser->setVar('teststring', 'Hello World');
        $this->assertSame("<h1>Hello World</h1>\n", $this->parser->render('template1'));
    }

    public function testParseString(): void
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

    public function testParseStringMissingData(): void
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

    public function testParseStringUnusedData(): void
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

    public function testParseNoTemplate(): void
    {
        $this->assertSame('', $this->parser->renderString(''));
    }

    public function testParseArraySingle(): void
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

    public function testParseArrayMulti(): void
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

    public function testParseArrayNested(): void
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

    public function testParseArrayNestedObject(): void
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

    public function testParseLoop(): void
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

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5825
     */
    public function testParseLoopVariableWithParentheses(): void
    {
        $data = [
            'title'  => 'Super Heroes',
            'powers' => [
                ['name' => 'Tom'],
                ['name' => 'Dick'],
                ['name' => 'Henry'],
            ],
        ];

        $template = "{title}\n{powers}({name}) {/powers}";

        $this->parser->setData($data);
        $this->assertSame("Super Heroes\n(Tom) (Dick) (Henry) ", $this->parser->renderString($template));
    }

    public function testParseLoopObjectProperties(): void
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

    public function testParseIntegerPositive(): void
    {
        $data = [
            'title'  => 'Count:',
            'amount' => 5,
        ];

        $template = '{title} {amount}';

        $this->parser->setData($data, 'html');
        $this->assertSame('Count: 5', $this->parser->renderString($template));
    }

    public function testParseIntegerNegative(): void
    {
        $data = [
            'title'  => 'Count:',
            'amount' => -5,
        ];

        $template = '{title} {amount}';

        $this->parser->setData($data, 'html');
        $this->assertSame('Count: -5', $this->parser->renderString($template));
    }

    public function testParseFloatPositive(): void
    {
        $data = [
            'title'  => 'Rate:',
            'amount' => 5.5,
        ];

        $template = '{title} {amount}';

        $this->parser->setData($data, 'html');
        $this->assertSame('Rate: 5.5', $this->parser->renderString($template));
    }

    public function testParseFloatNegative(): void
    {
        $data = [
            'title'  => 'Rate:',
            'amount' => -5.5,
        ];

        $template = '{title} {amount}';

        $this->parser->setData($data, 'html');
        $this->assertSame('Rate: -5.5', $this->parser->renderString($template));
    }

    public function testParseNull(): void
    {
        $data = [
            'title'  => 'Ticks:',
            'amount' => null,
        ];

        $template = '{title} {amount}';

        $this->parser->setData($data, 'html');
        $this->assertSame('Ticks: ', $this->parser->renderString($template));
    }

    public function testParseLoopEntityProperties(): void
    {
        $power = new class () extends Entity {
            public string $foo    = 'bar';
            protected string $bar = 'baz';

            /**
             * @return array<string, mixed>
             */
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

    public function testParseLoopEntityObjectProperties(): void
    {
        $power = new class () extends Entity {
            protected $attributes = [
                'foo'     => 'bar',
                'bar'     => 'baz',
                'obj1'    => null,
                'obj2'    => null,
                'bobbles' => [],
            ];

            public function __construct()
            {
                parent::__construct();

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

    public function testMismatchedVarPair(): void
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

    /**
     * @param mixed      $value
     * @param mixed|null $expected
     */
    #[DataProvider('provideEscHandling')]
    public function testEscHandling($value, $expected = null): void
    {
        $expected ??= $value;
        $this->assertSame($expected, \esc($value));
    }

    /**
     * @return iterable<string, list<mixed>>
     */
    public static function provideEscHandling(): iterable
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
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/3726
     */
    public function testParseSimilarVariableNames(): void
    {
        $template = '{foo} {foo_bar}';

        $this->parser->setData(['foo' => 'bar', 'foo_bar' => 'foo-bar'], 'raw');
        $this->assertSame('bar foo-bar', $this->parser->renderString($template));
    }

    public function testParsePairSimilarVariableNames(): void
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

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/788
     */
    public function testEscapingRespectsSetDataRaw(): void
    {
        $template = '{ foo }';

        $this->parser->setData(['foo' => '<script>'], 'raw');
        $this->assertSame('<script>', $this->parser->renderString($template));
    }

    public function testEscapingSetDataWithOtherContext(): void
    {
        $template = '{ foo }';

        $this->parser->setData(['foo' => 'http://foo.com'], 'url');
        $this->assertSame('http%3A%2F%2Ffoo.com', $this->parser->renderString($template));
    }

    public function testNoEscapingSetData(): void
    {
        $template = '{ foo | noescape}';

        $this->parser->setData(['foo' => 'http://foo.com'], 'unknown'); // @phpstan-ignore argument.type (deliberately invalid context)
        $this->assertSame('http://foo.com', $this->parser->renderString($template));
    }

    public function testAutoEscaping(): void
    {
        $this->parser->setData(['foo' => 'http://foo.com'], 'unknown'); // @phpstan-ignore argument.type (deliberately invalid context)

        $this->assertSame('html', $this->parser->shouldAddEscaping('{ foo | this | that }'));
    }

    public function testAutoEscapingNot(): void
    {
        $this->parser->setData(['foo' => 'http://foo.com'], 'unknown'); // @phpstan-ignore argument.type (deliberately invalid context)
        $this->assertFalse($this->parser->shouldAddEscaping('{ foo | noescape }'));
    }

    public function testFilterWithNoArgument(): void
    {
        $data = [
            'that_thing' => '<script>alert("ci4")</script>',
        ];

        $template = '{ that_thing|esc }';

        $this->parser->setData($data);
        $this->assertSame('&lt;script&gt;alert(&quot;ci4&quot;)&lt;/script&gt;', $this->parser->renderString($template));
    }

    public function testFilterWithArgument(): void
    {
        $date = time();

        $data = [
            'my_date' => $date,
        ];

        $template = '{ my_date| date(Y-m-d ) }';

        $this->parser->setData($data);
        $this->assertSame(date('Y-m-d', $date), $this->parser->renderString($template));
    }

    public function testParserEscapesDataDefaultsToHTML(): void
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

    public function testParserNoEscape(): void
    {
        $data = [
            'title' => '<script>Heroes</script>',
        ];

        $template = '{! title !}';
        $this->parser->setData($data);
        $this->assertSame('<script>Heroes</script>', $this->parser->renderString($template));
    }

    /**
     * @param array<string, mixed> $data
     */
    #[DataProvider('provideParserDoesNotInterpretSyntaxInSubstitutionValues')]
    public function testParserDoesNotInterpretSyntaxInSubstitutionValues(
        string $template,
        array $data,
        string $expected,
    ): void {
        $this->parser->setData($data, 'html');

        $this->assertSame($expected, $this->parser->renderString($template));
    }

    /**
     * @return iterable<string, array{string, array<string, mixed>, string}>
     */
    public static function provideParserDoesNotInterpretSyntaxInSubstitutionValues(): iterable
    {
        $escapedScript = '&lt;script&gt;alert(document.domain)&lt;/script&gt;';

        yield 'top-level no-escape delimiters' => [
            '{first}|{second}',
            [
                'first'  => '{!second!}',
                'second' => '<script>alert(document.domain)</script>',
            ],
            '{!second!}|' . $escapedScript,
        ];

        yield 'top-level filter' => [
            '{first}|{second}',
            [
                'first'  => '{second|upper}',
                'second' => '<script>alert(document.domain)</script>',
            ],
            '{second|upper}|' . $escapedScript,
        ];

        yield 'loop no-escape delimiters' => [
            '{comments}<b>{author}</b>: {text}{/comments}',
            [
                'comments' => [[
                    'author' => '{!text!}',
                    'text'   => '<script>alert(document.domain)</script>',
                ]],
            ],
            '<b>{!text!}</b>: ' . $escapedScript,
        ];

        yield 'loop unknown filter' => [
            '{comments}<b>{author}</b>: {text}{/comments}',
            [
                'comments' => [[
                    'author' => '{text|raw}',
                    'text'   => '<script>alert(document.domain)</script>',
                ]],
            ],
            '<b>{text|raw}</b>: ' . $escapedScript,
        ];

        yield 'loop to top-level no-escape delimiters' => [
            '{comments}{author}{/comments}|{text}',
            [
                'comments' => [['author' => '{!text!}']],
                'text'     => '<script>alert(document.domain)</script>',
            ],
            '{!text!}|' . $escapedScript,
        ];

        yield 'nested loop to outer row' => [
            '{comments}{replies}[{reply}]{/replies} by {author}{/comments}',
            [
                'comments' => [[
                    'replies' => [['reply' => '{author|raw}']],
                    'author'  => '<script>alert(document.domain)</script>',
                ]],
            ],
            '[{author|raw}] by ' . $escapedScript,
        ];

        yield 'repeated nested pairs with different templates' => [
            '{comments}{replies}[{reply}] by {author}{/replies} again {replies}<i>{reply}</i> by {author}{/replies}{/comments}',
            [
                'comments' => [[
                    'replies' => [['reply' => '{author|raw}'], ['reply' => 'Second']],
                    'author'  => '<script>alert(document.domain)</script>',
                ]],
            ],
            '[{author|raw}] by ' . $escapedScript . '[Second] by ' . $escapedScript
                . ' again <i>{author|raw}</i> by ' . $escapedScript . '<i>Second</i> by ' . $escapedScript,
        ];

        yield 'value resembling an internal token' => [
            '{first}|{second}',
            [
                'first'  => "\x1A0123456789abcdef0123456789abcdef:0\x1A",
                'second' => '<script>alert(document.domain)</script>',
            ],
            "\x1A0123456789abcdef0123456789abcdef:0\x1A|" . $escapedScript,
        ];

        yield 'unescaped substitution' => [
            '{!first!}|{second}',
            ['first' => '{!second!}', 'second' => '<script>alert(document.domain)</script>'],
            '{!second!}|' . $escapedScript,
        ];

        yield 'syntax assembled across a substitution boundary' => [
            '{first}second!}|{second}',
            ['first' => '{!', 'second' => '<script>alert(document.domain)</script>'],
            '{!second!}|' . $escapedScript,
        ];

        yield 'repeated substitutions with different escaping' => [
            '{first}|{!first!}|{second}',
            ['first' => '<b>{!second!}</b>', 'second' => '<script>alert(document.domain)</script>'],
            '&lt;b&gt;{!second!}&lt;/b&gt;|<b>{!second!}</b>|' . $escapedScript,
        ];

        yield 'noparse marker in data' => [
            '{first}|{noparse}<b>literal</b>{/noparse}',
            ['first' => 'noparse_' . md5('<b>literal</b>')],
            'noparse_' . md5('<b>literal</b>') . '|<b>literal</b>',
        ];
    }

    public function testParserDoesNotInterpretSyntaxInFilteredValues(): void
    {
        $this->config->filters['syntax'] = static fn (mixed $value): string => '{!' . $value . '!}';
        $this->parser->setData(['first' => 'second', 'second' => '<b>unsafe</b>']);

        $this->assertSame(
            '{!second!}|&lt;b&gt;unsafe&lt;/b&gt;',
            $this->parser->renderString('{first|syntax}|{second}'),
        );
    }

    public function testParserDoesNotInterpretSyntaxWithCustomDelimiters(): void
    {
        $this->parser->setDelimiters('{{', '}}');
        $this->parser->setData(['first' => '{{!second!}}', 'second' => '<b>unsafe</b>']);

        $this->assertSame(
            '{{!second!}}|&lt;b&gt;unsafe&lt;/b&gt;',
            $this->parser->renderString('{{first}}|{{second}}'),
        );
    }

    public function testParserDoesNotInterpretSyntaxInRawContext(): void
    {
        $this->parser->setData(['first' => '<b>{!second!}</b>'], 'raw');
        $this->parser->setData(['second' => '<b>unsafe</b>'], 'html');

        $this->assertSame(
            '<b>{!second!}</b>|&lt;b&gt;unsafe&lt;/b&gt;',
            $this->parser->renderString('{first}|{second}'),
        );
    }

    public function testParserRestoresReplacementStateAfterNestedRender(): void
    {
        $this->config->filters['nested'] = fn (mixed $value): string => $this->parser->renderString((string) $value, saveData: false);
        $this->parser->setData([
            'first'  => 'before',
            'second' => '{inner}',
            'inner'  => '{!last!}',
            'last'   => '<b>unsafe</b>',
        ]);

        $this->assertSame(
            'before|{!last!}|&lt;b&gt;unsafe&lt;/b&gt;',
            $this->parser->renderString('{first}|{second|nested}|{last}', saveData: false),
        );
        $this->assertSame([], $this->getPrivateProperty($this->parser, 'replacementTokens'));
        $this->assertNull($this->getPrivateProperty($this->parser, 'replacementTokenPrefix'));
    }

    public function testParserRestoresReplacementStateAfterFilterException(): void
    {
        $exception                     = new RuntimeException('Filter failed.');
        $this->config->filters['fail'] = static fn (mixed $value): string => throw $exception;
        $this->parser->setData(['first' => 'before', 'second' => 'failure']);

        try {
            $this->parser->renderString('{first}|{second|fail}');
            $this->fail('The filter exception was not propagated.');
        } catch (RuntimeException $caught) {
            $this->assertSame($exception, $caught);
        }

        $this->assertSame([], $this->getPrivateProperty($this->parser, 'replacementTokens'));
        $this->assertNull($this->getPrivateProperty($this->parser, 'replacementTokenPrefix'));

        $this->parser->setData(['first' => '{!second!}', 'second' => '<b>unsafe</b>']);
        $this->assertSame(
            '{!second!}|&lt;b&gt;unsafe&lt;/b&gt;',
            $this->parser->renderString('{first}|{second}'),
        );
    }

    public function testParserNoEscapeAndDelimiterChange(): void
    {
        $this->parser->setDelimiters('{{', '}}');

        $data = [
            'title' => '<script>Heroes</script>',
        ];
        $this->parser->setData($data);

        $template = '{{! title !}}';
        $this->assertSame('<script>Heroes</script>', $this->parser->renderString($template));
    }

    public function testIgnoresComments(): void
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

    public function testNoParse(): void
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

    public function testIfConditionalTrue(): void
    {
        $data = [
            'doit'     => true,
            'dontdoit' => false,
        ];

        $template = '{if $doit}Howdy{endif}{ if $dontdoit === false}Welcome{ endif }';
        $this->parser->setData($data);

        $this->assertSame('HowdyWelcome', $this->parser->renderString($template));
    }

    public function testElseConditionalFalse(): void
    {
        $data = [
            'doit' => true,
        ];

        $template = '{if $doit}Howdy{else}Welcome{ endif }';
        $this->parser->setData($data);

        $this->assertSame('Howdy', $this->parser->renderString($template));
    }

    public function testElseConditionalTrue(): void
    {
        $data = [
            'doit' => false,
        ];

        $template = '{if $doit}Howdy{else}Welcome{ endif }';
        $this->parser->setData($data);

        $this->assertSame('Welcome', $this->parser->renderString($template));
    }

    public function testElseifConditionalTrue(): void
    {
        $data = [
            'doit'     => false,
            'dontdoit' => true,
        ];

        $template = '{if $doit}Howdy{elseif $dontdoit}Welcome{ endif }';
        $this->parser->setData($data);

        $this->assertSame('Welcome', $this->parser->renderString($template));
    }

    public function testConditionalBadSyntax(): void
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

    public function testWontParsePHP(): void
    {
        $template = "<?php echo 'Foo' ?> - <?= 'Bar' ?>";
        $this->assertSame('&lt;?php echo \'Foo\' ?&gt; - &lt;?= \'Bar\' ?&gt;', $this->parser->renderString($template));
    }

    public function testParseHandlesSpaces(): void
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

    public function testParseRuns(): void
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

    public function testCanAddAndRemovePlugins(): void
    {
        $this->parser->addPlugin('first', static fn ($str): array|string => $str);

        $setParsers = $this->getPrivateProperty($this->parser, 'plugins');

        $this->assertArrayHasKey('first', $setParsers);

        $this->parser->removePlugin('first');

        $setParsers = $this->getPrivateProperty($this->parser, 'plugins');

        $this->assertArrayNotHasKey('first', $setParsers);
    }

    #[DataProvider('providePreviousURLDoesNotInterpretSyntaxFromReferer')]
    public function testPreviousURLDoesNotInterpretSyntaxFromReferer(string $syntax): void
    {
        $server     = service('superglobals')->getServerArray();
        $hadSession = isset($_SESSION);
        $session    = $_SESSION ?? [];

        try {
            unset($_SESSION);
            service('superglobals')->setServer('HTTP_REFERER', 'https://example.com/page?x=' . $syntax);
            Services::resetSingle('request');

            $this->parser->addPlugin('probe', static fn (): string => '<script>unexpected</script>');
            $this->parser->setData(['bio' => '<script>alert(document.domain)</script>']);

            $this->assertSame(
                '<a href="https://example.com/page?x=' . $syntax . '">back</a>|&lt;script&gt;alert(document.domain)&lt;/script&gt;',
                $this->parser->renderString('<a href="{+ previous_url +}">back</a>|{bio}'),
            );
        } finally {
            service('superglobals')->setServerArray($server);
            if ($hadSession) {
                $_SESSION = $session;
            } else {
                unset($_SESSION);
            }

            Services::resetSingle('request');
        }
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function providePreviousURLDoesNotInterpretSyntaxFromReferer(): iterable
    {
        yield 'variable' => ['{!bio!}'];

        yield 'later plugin' => ['{+probe+}'];
    }

    public function testValidationErrorsDoesNotInterpretSyntaxInMessage(): void
    {
        service('validation')->setError('email', 'Bad value: {!bio!}');
        $this->parser->setData(['bio' => '<script>alert(document.domain)</script>']);

        $output = $this->parser->renderString('{+ validation_errors field=email +}|{bio}');

        $this->assertStringContainsString('<span class="help-block">Bad value: {!bio!}</span>', $output);
        $this->assertStringContainsString('|&lt;script&gt;alert(document.domain)&lt;/script&gt;', $output);
        $this->assertStringNotContainsString('<script>', $output);
    }

    public function testBuiltInPluginUnderCustomAliasDoesNotInterpretSyntax(): void
    {
        $this->config->plugins['errors'] = '\\CodeIgniter\\View\\Plugins::validationErrors';
        $this->parser                    = new Parser($this->config, $this->viewsDir, $this->loader);
        service('validation')->setError('email', 'Bad value: {!bio!}');
        $this->parser->setData(['bio' => '<script>unexpected</script>']);

        $output = $this->parser->renderString('{+ errors field=email +}');

        $this->assertStringContainsString('Bad value: {!bio!}', $output);
        $this->assertStringNotContainsString('<script>', $output);
    }

    public function testOverriddenBuiltInPluginCanStillReturnTemplateCode(): void
    {
        $this->parser->addPlugin('previous_url', static fn (): string => '{name}');
        $this->parser->setData(['name' => 'Alice']);

        $this->assertSame('Alice', $this->parser->renderString('{+ previous_url +}'));
    }

    public function testPairedPluginCanStillTransformTemplateCode(): void
    {
        $this->parser->addPlugin('wrap', static fn (string $body): string => '<section>' . $body . '</section>', true);
        $this->parser->setData(['name' => '<b>Alice</b>']);

        $this->assertSame(
            '<section>&lt;b&gt;Alice&lt;/b&gt;</section>',
            $this->parser->renderString('{+ wrap +}{name}{+ /wrap +}'),
        );
    }

    public function testParserPluginNoMatches(): void
    {
        $template = 'hit:it';

        $this->assertSame('hit:it', $this->parser->renderString($template));
    }

    public function testParserPluginNoParams(): void
    {
        $this->parser->addPlugin('hit:it', static fn (string $str): string => str_replace('here', 'Hip to the Hop', $str), true);

        $template = '{+ hit:it +} stuff here {+ /hit:it +}';

        $this->assertSame(' stuff Hip to the Hop ', $this->parser->renderString($template));
    }

    public function testParserPluginClosure(): void
    {
        $config                   = $this->config;
        $config->plugins['hello'] = static fn (array $params = []): string => 'Hello, ' . trim($params[0]);

        $this->parser = new Parser($config, $this->viewsDir, $this->loader);

        $template = '{+ hello world +}';

        $this->assertSame('Hello, world', $this->parser->renderString($template));
    }

    public function testParserPluginParams(): void
    {
        $this->parser->addPlugin('growth', static function ($str, array $params): string {
            $step  = $params['step'] ?? 1;
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

    public function testParserSingleTag(): void
    {
        $this->parser->addPlugin('hit:it', static fn (): string => 'Hip to the Hop', false);

        $template = '{+ hit:it +}';

        $this->assertSame('Hip to the Hop', $this->parser->renderString($template));
    }

    public function testParserSingleTagWithParams(): void
    {
        $this->parser->addPlugin('hit:it', static fn (array $params = []): string => "{$params['first']} to the {$params['last']}", false);

        $template = '{+ hit:it first=foo last=bar +}';

        $this->assertSame('foo to the bar', $this->parser->renderString($template));
    }

    public function testParserSingleTagWithSingleParams(): void
    {
        $this->parser->addPlugin('hit:it', static fn (array $params = []): string => "{$params[0]} to the {$params[1]}", false);

        $template = '{+ hit:it foo bar +}';

        $this->assertSame('foo to the bar', $this->parser->renderString($template));
    }

    public function testParserSingleTagWithQuotedParams(): void
    {
        $this->parser->addPlugin('count', static function (array $params = []): string {
            $out = '';

            foreach ($params as $index => $param) {
                $out .= "{$index}. {$param} ";
            }

            return $out;
        }, false);

        $template = '{+ count "foo bar" baz "foo bar" +}';

        $this->assertSame('0. foo bar 1. baz 2. foo bar ', $this->parser->renderString($template));
    }

    public function testParserSingleTagWithNamedParams(): void
    {
        $this->parser->addPlugin('read_params', static function (array $params = []): string {
            $out = '';

            foreach ($params as $index => $param) {
                $out .= "{$index}: {$param}. ";
            }

            return $out;
        }, false);

        $template = '{+ read_params title="Hello world" page=5 email=test@test.net +}';

        $this->assertSame('title: Hello world. page: 5. email: test@test.net. ', $this->parser->renderString($template));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/705
     */
    public function testParseLoopWithDollarSign(): void
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

    public function testCachedRender(): void
    {
        $this->parser->setVar('teststring', 'Hello World');

        $expected = "<h1>Hello World</h1>\n";
        $output   = $this->parser->render('template1', ['cache' => 10, 'cache_name' => 'HelloWorld']);
        $this->assertSame($expected, $output);
        // this second renderings should go thru the cache
        $this->assertSame($expected, $this->parser->render('template1', ['cache' => 10, 'cache_name' => 'HelloWorld']));
    }

    public function testRenderFindsView(): void
    {
        $this->parser->setData(['testString' => 'Hello World']);
        $this->assertSame("<h1>Hello World</h1>\n", $this->parser->render('Simpler'));
    }

    public function testRenderCannotFindView(): void
    {
        $this->expectException(ViewException::class);
        $this->expectExceptionMessageMatches('!\AInvalid file: \"(?:.+)View(?:/|\\\\)Views(?:/|\\\\)Simplest\.php\"\z!');

        $this->parser->setData(['testString' => 'Hello World']);
        $this->parser->render('Simplest');
    }

    public function testRenderSavingData(): void
    {
        $expected = "<h1>Hello World</h1>\n";

        $this->parser->setData(['testString' => 'Hello World']);
        $this->assertSame($expected, $this->parser->render('Simpler', [], false));
        $this->assertArrayNotHasKey('testString', $this->parser->getData());

        $this->parser->setData(['testString' => 'Hello World']);
        $this->assertSame($expected, $this->parser->render('Simpler', [], true));
        $this->assertArrayHasKey('testString', $this->parser->getData());
    }

    public function testRenderStringSavingData(): void
    {
        $expected = '<h1>Hello World</h1>';
        $pattern  = '<h1>{testString}</h1>';

        $this->parser->setData(['testString' => 'Hello World']);
        $this->assertSame($expected, $this->parser->renderString($pattern, [], false));
        $this->assertArrayNotHasKey('testString', $this->parser->getData());
        // last set data is not saved
        $this->parser->setData(['testString' => 'Hello World']);
        $this->assertSame($expected, $this->parser->renderString($pattern, [], true));
        $this->assertArrayHasKey('testString', $this->parser->getData());
    }

    public function testRenderFindsOtherView(): void
    {
        $this->parser->setData(['testString' => 'Hello World']);
        $expected = '<h1>Hello World</h1>';
        $this->assertSame($expected, $this->parser->render('Simpler.html'));
    }

    public function testChangedConditionalDelimitersTrue(): void
    {
        $this->parser->setConditionalDelimiters('{%', '%}');

        $data = [
            'doit'     => true,
            'dontdoit' => false,
        ];
        $this->parser->setData($data);

        $template = '{% if $doit %}Howdy{% endif %}{% if $dontdoit === false %}Welcome{% endif %}';
        $output   = $this->parser->renderString($template);

        $this->assertSame('HowdyWelcome', $output);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5831
     */
    public function testChangeConditionalDelimitersWorkWithJavaScriptCode(): void
    {
        $this->parser->setConditionalDelimiters('{%', '%}');

        $data = [
            'message' => 'Warning!',
        ];
        $this->parser->setData($data);

        $template = <<<'EOL'
            <script type="text/javascript">
             var f = function() {
                 if (true) {
                     alert('{message}');
                 }
             }
            </script>
            EOL;
        $expected = <<<'EOL'
            <script type="text/javascript">
             var f = function() {
                 if (true) {
                     alert('Warning!');
                 }
             }
            </script>
            EOL;
        $this->assertSame($expected, $this->parser->renderString($template));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/9245
     */
    public function testParseSameArrayKeyName(): void
    {
        $data = [
            'type'   => 'Super Powers',
            'powers' => [
                [
                    'type' => 'invisibility',
                ],
            ],
        ];

        $template = '{type} like {powers}{type}{/powers}';

        $this->parser->setData($data);
        $this->assertSame('Super Powers like invisibility', $this->parser->renderString($template));
    }

    public function testParseSameArrayKeyNameNested(): void
    {
        $data = [
            'title'   => 'My title',
            'similar' => [
                ['items' => [
                    [
                        'title' => 'My similar title',
                    ],
                ],
                ],
            ],
        ];

        $template = '{title} with similar item {similar}{items}{title}{/items}{/similar}';

        $this->parser->setData($data);
        $this->assertSame('My title with similar item My similar title', $this->parser->renderString($template));
    }
}
