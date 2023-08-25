<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use CodeIgniter\Test\CIUnitTestCase;
use Config\App;

/**
 * This should be the same as RequestTest,
 * except also testing the methods added by CLIRequest
 *
 * @backupGlobals enabled
 *
 * @internal
 *
 * @group Others
 */
final class CLIRequestTest extends CIUnitTestCase
{
    private CLIRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new CLIRequest(new App());

        $_POST = [];
        $_GET  = [];
    }

    public function testParsingSegments(): void
    {
        $_SERVER['argv'] = [
            'index.php',
            'users',
            '21',
            'profile',
            '-foo',
            'bar',
        ];

        // reinstantiate it to force parsing
        $this->request = new CLIRequest(new App());

        $segments = [
            'users',
            '21',
            'profile',
        ];
        $this->assertSame($segments, $this->request->getSegments());
    }

    public function testParsingSegmentsWithHTMLMetaChars(): void
    {
        $_SERVER['argv'] = [
            'index.php',
            'users',
            '21',
            'abc < def',
            "McDonald's",
            '<s>aaa</s>',
        ];

        // reinstantiate it to force parsing
        $this->request = new CLIRequest(new App());

        $segments = [
            'users',
            '21',
            'abc < def',
            "McDonald's",
            '<s>aaa</s>',
        ];
        $this->assertSame($segments, $this->request->getSegments());
    }

    public function testParsingOptions(): void
    {
        $_SERVER['argv'] = [
            'index.php',
            'users',
            '21',
            'profile',
            '--foo',
            'bar',
            '--foo-bar',
            'yes',
        ];

        // reinstantiate it to force parsing
        $this->request = new CLIRequest(new App());

        $options = [
            'foo'     => 'bar',
            'foo-bar' => 'yes',
        ];
        $this->assertSame($options, $this->request->getOptions());
    }

    public function testParsingOptionDetails(): void
    {
        $_SERVER['argv'] = [
            'index.php',
            'users',
            '21',
            'profile',
            '--foo',
            'bar',
        ];

        // reinstantiate it to force parsing
        $this->request = new CLIRequest(new App());

        $this->assertSame('bar', $this->request->getOption('foo'));
        $this->assertNull($this->request->getOption('notthere'));
    }

    public function testParsingOptionString(): void
    {
        $_SERVER['argv'] = [
            'index.php',
            'users',
            '21',
            'profile',
            '--foo',
            'bar',
            '--baz',
            'queue some stuff',
        ];

        // reinstantiate it to force parsing
        $this->request = new CLIRequest(new App());

        $this->assertSame('-foo bar -baz "queue some stuff"', $this->request->getOptionString());
        $this->assertSame('--foo bar --baz "queue some stuff"', $this->request->getOptionString(true));
    }

    public function testParsingNoOptions(): void
    {
        $_SERVER['argv'] = [
            'index.php',
            'users',
            '21',
            'profile',
        ];

        // reinstantiate it to force parsing
        $this->request = new CLIRequest(new App());

        $expected = '';
        $this->assertSame($expected, $this->request->getOptionString());
    }

    public function testParsingArgs(): void
    {
        $_SERVER['argv'] = [
            'spark',
            'command',
            'param1',
            'param2',
            '--opt1',
            'opt1val',
            '--opt-2',
            'opt 2 val',
            'param3',
        ];

        // reinstantiate it to force parsing
        $this->request = new CLIRequest(new App());

        $options = [
            'command',
            'param1',
            'param2',
            'opt1'  => 'opt1val',
            'opt-2' => 'opt 2 val',
            'param3',
        ];
        $this->assertSame($options, $this->request->getArgs());
    }

    public function testParsingPath(): void
    {
        $_SERVER['argv'] = [
            'index.php',
            'users',
            '21',
            'profile',
            '--foo',
            'bar',
        ];

        // reinstantiate it to force parsing
        $this->request = new CLIRequest(new App());

        $this->assertSame('users/21/profile', $this->request->getPath());
    }

    public function testParsingMalformed(): void
    {
        $_SERVER['argv'] = [
            'index.php',
            'users',
            '21',
            'pro-file',
            '--foo',
            'bar',
            '--baz',
            'queue some stuff',
        ];

        // reinstantiate it to force parsing
        $this->request = new CLIRequest(new App());

        $this->assertSame('-foo bar -baz "queue some stuff"', $this->request->getOptionString());
        $this->assertSame('--foo bar --baz "queue some stuff"', $this->request->getOptionString(true));
        $this->assertSame('users/21/pro-file', $this->request->getPath());
    }

    public function testParsingMalformed2(): void
    {
        $_SERVER['argv'] = [
            'index.php',
            'users',
            '21',
            'profile',
            '--foo',
            'oops-bar',
            '--baz',
            'queue some stuff',
        ];

        // reinstantiate it to force parsing
        $this->request = new CLIRequest(new App());

        $this->assertSame('-foo oops-bar -baz "queue some stuff"', $this->request->getOptionString());
        $this->assertSame('--foo oops-bar --baz "queue some stuff"', $this->request->getOptionString(true));
        $this->assertSame('users/21/profile', $this->request->getPath());
    }

    public function testParsingMalformed3(): void
    {
        $_SERVER['argv'] = [
            'index.php',
            'users',
            '21',
            'profile',
            '--foo',
            'oops',
            'bar',
            '--baz',
            'queue some stuff',
        ];

        // reinstantiate it to force parsing
        $this->request = new CLIRequest(new App());

        $this->assertSame('-foo oops -baz "queue some stuff"', $this->request->getOptionString());
        $this->assertSame('--foo oops --baz "queue some stuff"', $this->request->getOptionString(true));
        $this->assertSame('users/21/profile/bar', $this->request->getPath());
    }

    public function testFetchGlobalsSingleValue(): void
    {
        $_POST['foo'] = 'bar';
        $_GET['bar']  = 'baz';

        $this->assertSame('bar', $this->request->fetchGlobal('post', 'foo'));
        $this->assertSame('baz', $this->request->fetchGlobal('get', 'bar'));
    }

    public function testFetchGlobalsReturnsNullWhenNotFound(): void
    {
        $this->assertNull($this->request->fetchGlobal('post', 'foo'));
    }

    public function testFetchGlobalsFiltersValues(): void
    {
        $this->request->setGlobal('post', [
            'foo' => 'bar<script>',
            'bar' => 'baz',
        ]);

        $this->assertSame('bar%3Cscript%3E', $this->request->fetchGlobal('post', 'foo', FILTER_SANITIZE_ENCODED));
        $this->assertSame('baz', $this->request->fetchGlobal('post', 'bar'));
    }

    public function testFetchGlobalsWithFilterFlag(): void
    {
        $this->request->setGlobal('post', [
            'foo' => '`bar<script>',
            'bar' => 'baz',
        ]);

        $this->assertSame('bar%3Cscript%3E', $this->request->fetchGlobal('post', 'foo', FILTER_SANITIZE_ENCODED, FILTER_FLAG_STRIP_BACKTICK));
        $this->assertSame('baz', $this->request->fetchGlobal('post', 'bar'));
    }

    public function testFetchGlobalReturnsAllWhenEmpty(): void
    {
        $post = [
            'foo' => 'bar',
            'bar' => 'baz',
            'xxx' => 'yyy',
            'yyy' => 'zzz',
        ];
        $this->request->setGlobal('post', $post);

        $this->assertSame($post, $this->request->fetchGlobal('post'));
    }

    public function testFetchGlobalFiltersAllValues(): void
    {
        $post = [
            'foo' => 'bar<script>',
            'bar' => 'baz<script>',
            'xxx' => 'yyy<script>',
            'yyy' => 'zzz<script>',
        ];
        $this->request->setGlobal('post', $post);
        $expected = [
            'foo' => 'bar%3Cscript%3E',
            'bar' => 'baz%3Cscript%3E',
            'xxx' => 'yyy%3Cscript%3E',
            'yyy' => 'zzz%3Cscript%3E',
        ];

        $this->assertSame($expected, $this->request->fetchGlobal('post', null, FILTER_SANITIZE_ENCODED));
    }

    public function testFetchGlobalFilterWithFlagAllValues(): void
    {
        $post = [
            'foo' => '`bar<script>',
            'bar' => '`baz<script>',
            'xxx' => '`yyy<script>',
            'yyy' => '`zzz<script>',
        ];
        $this->request->setGlobal('post', $post);
        $expected = [
            'foo' => 'bar%3Cscript%3E',
            'bar' => 'baz%3Cscript%3E',
            'xxx' => 'yyy%3Cscript%3E',
            'yyy' => 'zzz%3Cscript%3E',
        ];

        $this->assertSame($expected, $this->request->fetchGlobal('post', null, FILTER_SANITIZE_ENCODED, FILTER_FLAG_STRIP_BACKTICK));
    }

    public function testFetchGlobalReturnsSelectedKeys(): void
    {
        $post = [
            'foo' => 'bar',
            'bar' => 'baz',
            'xxx' => 'yyy',
            'yyy' => 'zzz',
        ];
        $this->request->setGlobal('post', $post);
        $expected = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];

        $this->assertSame($expected, $this->request->fetchGlobal('post', ['foo', 'bar']));
    }

    public function testFetchGlobalFiltersSelectedValues(): void
    {
        $post = [
            'foo' => 'bar<script>',
            'bar' => 'baz<script>',
            'xxx' => 'yyy<script>',
            'yyy' => 'zzz<script>',
        ];
        $this->request->setGlobal('post', $post);
        $expected = [
            'foo' => 'bar%3Cscript%3E',
            'bar' => 'baz%3Cscript%3E',
        ];

        $this->assertSame($expected, $this->request->fetchGlobal('post', ['foo', 'bar'], FILTER_SANITIZE_ENCODED));
    }

    public function testFetchGlobalFilterWithFlagSelectedValues(): void
    {
        $post = [
            'foo' => '`bar<script>',
            'bar' => '`baz<script>',
            'xxx' => '`yyy<script>',
            'yyy' => '`zzz<script>',
        ];
        $this->request->setGlobal('post', $post);
        $expected = [
            'foo' => 'bar%3Cscript%3E',
            'bar' => 'baz%3Cscript%3E',
        ];

        $this->assertSame($expected, $this->request->fetchGlobal('post', ['foo', 'bar'], FILTER_SANITIZE_ENCODED, FILTER_FLAG_STRIP_BACKTICK));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/353
     */
    public function testFetchGlobalReturnsArrayValues(): void
    {
        $post = [
            'ANNOUNCEMENTS' => [
                1 => [
                    'DETAIL' => 'asdf',
                ],
                2 => [
                    'DETAIL' => 'sdfg',
                ],
            ],
            'submit' => 'SAVE',
        ];
        $this->request->setGlobal('post', $post);
        $result = $this->request->fetchGlobal('post');

        $this->assertSame($post, $result);
        $this->assertIsArray($result['ANNOUNCEMENTS']);
        $this->assertCount(2, $result['ANNOUNCEMENTS']);
    }

    public function testFetchGlobalWithArrayTop(): void
    {
        $post = [
            'clients' => [
                'address' => [
                    'zipcode' => 90210,
                ],
            ],
        ];
        $this->request->setGlobal('post', $post);

        $this->assertSame(['address' => ['zipcode' => 90210]], $this->request->fetchGlobal('post', 'clients'));
    }

    public function testFetchGlobalWithArrayChildNumeric(): void
    {
        $post = [
            'clients' => [
                [
                    'address' => [
                        'zipcode' => 90210,
                    ],
                ],
                [
                    'address' => [
                        'zipcode' => 60610,
                    ],
                ],
            ],
        ];
        $this->request->setGlobal('post', $post);

        $this->assertSame(['zipcode' => 60610], $this->request->fetchGlobal('post', 'clients[1][address]'));
    }

    public function testFetchGlobalWithArrayChildElement(): void
    {
        $post = [
            'clients' => [
                'address' => [
                    'zipcode' => 90210,
                ],
            ],
        ];
        $this->request->setGlobal('post', $post);

        $this->assertSame(['zipcode' => 90210], $this->request->fetchGlobal('post', 'clients[address]'));
        $this->assertNull($this->request->fetchGlobal('post', 'clients[zipcode]'));
    }

    public function testFetchGlobalWithKeylessArrayChildElement(): void
    {
        $post = [
            'clients' => [
                'address' => [
                    'zipcode' => 90210,
                ],
                'stuff' => [['a']],
            ],
        ];
        $this->request->setGlobal('post', $post);

        $this->assertSame([['a']], $this->request->fetchGlobal('post', 'clients[stuff]'));
    }

    public function testFetchGlobalWithArrayLastElement(): void
    {
        $post = ['clients' => ['address' => ['zipcode' => 90210]]];
        $this->request->setGlobal('post', $post);

        $this->assertSame('90210', $this->request->fetchGlobal('post', 'clients[address][zipcode]'));
    }

    public function testFetchGlobalWithEmptyNotation(): void
    {
        $expected = [
            ['address' => ['zipcode' => '90210']],
            ['address' => ['zipcode' => '60610']],
        ];
        $post = ['clients' => $expected];
        $this->request->setGlobal('post', $post);

        $this->assertSame($expected, $this->request->fetchGlobal('post', 'clients[]'));
    }

    public static function provideValidIPAddress(): iterable
    {
        return [
            'empty' => [
                false,
                '',
            ],
            'zero' => [
                false,
                0,
            ],
            'large_ipv4' => [
                false,
                '256.256.256.999',
                'ipv4',
            ],
            'good_ipv4' => [
                true,
                '100.100.100.0',
                'ipv4',
            ],
            'good_default' => [
                true,
                '100.100.100.0',
            ],
            'zeroed_ipv4' => [
                true,
                '0.0.0.0',
            ],
            'large_ipv6' => [
                false,
                'h123:0000:0000:0000:0000:0000:0000:0000',
                'ipv6',
            ],
            'good_ipv6' => [
                true,
                '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            ],
            'confused_ipv6' => [
                false,
                '255.255.255.255',
                'ipv6',
            ],
        ];
    }

    /**
     * @dataProvider provideValidIPAddress
     *
     * @param mixed      $expected
     * @param mixed      $address
     * @param mixed|null $type
     */
    public function testValidIPAddress($expected, $address, $type = null): void
    {
        $this->assertSame($expected, $this->request->isValidIP($address, $type));
    }

    public function testGetIPAddressDefault(): void
    {
        $this->assertSame('0.0.0.0', $this->request->getIPAddress());
    }

    public function testMethodReturnsRightStuff(): void
    {
        // Defaults method to CLI now.
        $this->assertSame('cli', $this->request->getMethod());
        $this->assertSame('CLI', $this->request->getMethod(true));
    }

    public function testMethodIsCliReturnsAlwaysTrue(): void
    {
        $this->assertTrue($this->request->isCLI());
    }

    public function testGetGet(): void
    {
        $this->assertSame([], $this->request->getGet());
        $this->assertNull($this->request->getGet('test'));
        $this->assertSame([], $this->request->getGet(['test', 'abc']));
    }

    public function testGetPost(): void
    {
        $this->assertSame([], $this->request->getPost());
    }

    public function testGetPostGet(): void
    {
        $this->assertSame([], $this->request->getPostGet());
    }

    public function testGetGetPost(): void
    {
        $this->assertSame([], $this->request->getGetPost());
    }

    public function testGetLocale(): void
    {
        $this->assertSame('en', $this->request->getLocale());
    }

    public function testGetCookie(): void
    {
        $this->assertNull($this->request->getCookie('TESTY'));

        $this->assertSame($this->request->getCookie(), []);
    }

    public function testIs(): void
    {
        $this->assertFalse($this->request->is('get'));
    }
}
