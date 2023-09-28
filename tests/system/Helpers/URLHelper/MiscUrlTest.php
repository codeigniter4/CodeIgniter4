<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Helpers\URLHelper;

use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\SiteURIFactory;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Router\Exceptions\RouterException;
use CodeIgniter\Superglobals;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use InvalidArgumentException;

/**
 * @backupGlobals enabled
 *
 * @internal
 *
 * @group Others
 */
final class MiscUrlTest extends CIUnitTestCase
{
    private App $config;

    protected function setUp(): void
    {
        parent::setUp();

        Services::reset(true);

        // Set a common base configuration (overriden by individual tests)
        $this->config            = new App();
        $this->config->baseURL   = 'http://example.com/';
        $this->config->indexPage = 'index.php';
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $_SERVER = [];
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @group SeparateProcess
     */
    public function testPreviousURLUsesSessionFirst(): void
    {
        $uri1 = 'http://example.com/one?two';
        $uri2 = 'http://example.com/two?foo';

        $_SERVER['HTTP_REFERER'] = $uri1;
        session()->set('_ci_previous_url', $uri2);

        $this->config->baseURL = 'http://example.com/public';

        $uri = 'http://example.com/public';
        $this->createRequest($uri);

        $this->assertSame($uri2, previous_url());
    }

    private function createRequest(string $uri): void
    {
        $factory = new SiteURIFactory($this->config, new Superglobals());

        $uri = $factory->createFromString($uri);

        $request = new IncomingRequest($this->config, $uri, null, new UserAgent());
        Services::injectMock('request', $request);

        Factories::injectMock('config', 'App', $this->config);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @group SeparateProcess
     */
    public function testPreviousURLUsesRefererIfNeeded(): void
    {
        $uri1 = 'http://example.com/one?two';

        $_SERVER['HTTP_REFERER'] = $uri1;

        $this->config->baseURL = 'http://example.com/public';

        $uri = 'http://example.com/public';
        $this->createRequest($uri);

        $this->assertSame($uri1, previous_url());
    }

    // Test index_page

    public function testIndexPage(): void
    {
        $uri = 'http://example.com/';
        $this->createRequest($uri);

        $this->assertSame('index.php', index_page());
    }

    public function testIndexPageAlt(): void
    {
        $this->config->indexPage = 'banana.php';

        $uri = 'http://example.com/';
        $this->createRequest($uri);

        $this->assertSame('banana.php', index_page($this->config));
    }

    // Test anchor

    public static function provideAnchor(): iterable
    {
        return [
            'normal01' => [
                '<a href="http://example.com/index.php">http://example.com/index.php</a>',
                '',
            ],
            'normal02' => [
                '<a href="http://example.com/index.php">Bananas</a>',
                '/',
                'Bananas',
            ],
            'normal03' => [
                '<a href="http://example.com/index.php" fruit="peach">http://example.com/index.php</a>',
                '/',
                '',
                'fruit="peach"',
            ],
            'normal04' => [
                '<a href="http://example.com/index.php" fruit=peach>Bananas</a>',
                '/',
                'Bananas',
                'fruit=peach',
            ],
            'normal05' => [
                '<a href="http://example.com/index.php" fruit="peach">http://example.com/index.php</a>',
                '/',
                '',
                ['fruit' => 'peach'],
            ],
            'normal06' => [
                '<a href="http://example.com/index.php" fruit="peach">Bananas</a>',
                '/',
                'Bananas',
                ['fruit' => 'peach'],
            ],
            'normal07' => [
                '<a href="http://example.com/index.php">http://example.com/index.php</a>',
                '/',
            ],
        ];
    }

    /**
     * @dataProvider provideAnchor
     *
     * @param mixed $expected
     * @param mixed $uri
     * @param mixed $title
     * @param mixed $attributes
     */
    public function testAnchor($expected = '', $uri = '', $title = '', $attributes = ''): void
    {
        $uriString = 'http://example.com/';
        $this->createRequest($uriString);

        $this->assertSame($expected, anchor($uri, $title, $attributes, $this->config));
    }

    public static function provideAnchorNoindex(): iterable
    {
        return [
            'noindex01' => [
                '<a href="http://example.com">http://example.com</a>',
                '',
            ],
            'noindex02' => [
                '<a href="http://example.com">Bananas</a>',
                '',
                'Bananas',
            ],
            'noindex03' => [
                '<a href="http://example.com" fruit="peach">http://example.com</a>',
                '',
                '',
                'fruit="peach"',
            ],
            'noindex04' => [
                '<a href="http://example.com" fruit=peach>Bananas</a>',
                '',
                'Bananas',
                'fruit=peach',
            ],
            'noindex05' => [
                '<a href="http://example.com" fruit="peach">http://example.com</a>',
                '',
                '',
                ['fruit' => 'peach'],
            ],
            'noindex06' => [
                '<a href="http://example.com" fruit="peach">Bananas</a>',
                '',
                'Bananas',
                ['fruit' => 'peach'],
            ],
            'noindex07' => [
                '<a href="http://example.com">http://example.com</a>',
                '/',
            ],
            'noindex08' => [
                '<a href="http://example.com" class="btn btn-primary">http://example.com</a>',
                '',
                '',
                ['class' => 'btn btn-primary'],
            ],
        ];
    }

    /**
     * @dataProvider provideAnchorNoindex
     *
     * @param mixed $expected
     * @param mixed $uri
     * @param mixed $title
     * @param mixed $attributes
     */
    public function testAnchorNoindex($expected = '', $uri = '', $title = '', $attributes = ''): void
    {
        $this->config->indexPage = '';

        $uriString = 'http://example.com/';
        $this->createRequest($uriString);

        $this->assertSame($expected, anchor($uri, $title, $attributes, $this->config));
    }

    public static function provideAnchorTargetted(): iterable
    {
        return [
            'subpage01' => [
                '<a href="http://example.com/mush">http://example.com/mush</a>',
                '/mush',
            ],
            'subpage02' => [
                '<a href="http://example.com/mush">Bananas</a>',
                '/mush',
                'Bananas',
            ],
            'subpage03' => [
                '<a href="http://example.com/mush" fruit="peach">http://example.com/mush</a>',
                '/mush',
                '',
                'fruit="peach"',
            ],
            'subpage04' => [
                '<a href="http://example.com/mush" fruit=peach>Bananas</a>',
                '/mush',
                'Bananas',
                'fruit=peach',
            ],
            'subpage05' => [
                '<a href="http://example.com/mush" fruit="peach">http://example.com/mush</a>',
                '/mush',
                '',
                ['fruit' => 'peach'],
            ],
            'subpage06' => [
                '<a href="http://example.com/mush" fruit="peach">Bananas</a>',
                '/mush',
                'Bananas',
                ['fruit' => 'peach'],
            ],
        ];
    }

    /**
     * @dataProvider provideAnchorTargetted
     *
     * @param mixed $expected
     * @param mixed $uri
     * @param mixed $title
     * @param mixed $attributes
     */
    public function testAnchorTargetted($expected = '', $uri = '', $title = '', $attributes = ''): void
    {
        $this->config->indexPage = '';

        $uriString = 'http://example.com/';
        $this->createRequest($uriString);

        $this->assertSame($expected, anchor($uri, $title, $attributes, $this->config));
    }

    public static function provideAnchorExamples(): iterable
    {
        return [
            'egpage01' => [
                '<a href="http://example.com/index.php/news/local/123" title="News title">My News</a>',
                'news/local/123',
                'My News',
                'title="News title"',
            ],
            'egpage02' => [
                '<a href="http://example.com/index.php/news/local/123" title="The best news!">My News</a>',
                'news/local/123',
                'My News',
                ['title' => 'The best news!'],
            ],
            'egpage03' => [
                '<a href="http://example.com/index.php">Click here</a>',
                '',
                'Click here',
            ],
            'egpage04' => [
                '<a href="http://example.com/index.php">Click here</a>',
                '/',
                'Click here',
            ],
        ];
    }

    /**
     * @dataProvider provideAnchorExamples
     *
     * @param mixed $expected
     * @param mixed $uri
     * @param mixed $title
     * @param mixed $attributes
     */
    public function testAnchorExamples($expected = '', $uri = '', $title = '', $attributes = ''): void
    {
        $uriString = 'http://example.com/';
        $this->createRequest($uriString);

        $this->assertSame($expected, anchor($uri, $title, $attributes, $this->config));
    }

    // Test anchor_popup

    public static function provideAnchorPopup(): iterable
    {
        return [
            'normal01' => [
                '<a href="http://example.com/index.php" onclick="window.open(\'http://example.com/index.php\', \'_blank\'); return false;">http://example.com/index.php</a>',
                '',
            ],
            'normal02' => [
                '<a href="http://example.com/index.php" onclick="window.open(\'http://example.com/index.php\', \'_blank\'); return false;">Bananas</a>',
                '/',
                'Bananas',
            ],
            'normal07' => [
                '<a href="http://example.com/index.php" onclick="window.open(\'http://example.com/index.php\', \'_blank\'); return false;">http://example.com/index.php</a>',
                '/',
            ],
            'normal08' => [
                '<a href="http://example.com/index.php/news/local/123" onclick="window.open(\'http://example.com/index.php/news/local/123\', \'_blank\', \'width=800,height=600,scrollbars=yes,menubar=no,status=yes,resizable=yes,screenx=0,screeny=0\'); return false;">Click Me!</a>',
                'news/local/123',
                'Click Me!',
                [
                    'width'       => 800,
                    'height'      => 600,
                    'scrollbars'  => 'yes',
                    'status'      => 'yes',
                    'resizable'   => 'yes',
                    'screenx'     => 0,
                    'screeny'     => 0,
                    'window_name' => '_blank',
                ],
            ],
            'normal09' => [
                '<a href="http://example.com/index.php/news/local/123" onclick="window.open(\'http://example.com/index.php/news/local/123\', \'_blank\', \'width=800,height=600,scrollbars=yes,menubar=no,status=yes,resizable=yes,screenx=0,screeny=0\'); return false;">Click Me!</a>',
                'news/local/123',
                'Click Me!',
                [],
            ],
        ];
    }

    /**
     * @dataProvider provideAnchorPopup
     *
     * @param mixed $expected
     * @param mixed $uri
     * @param mixed $title
     * @param mixed $attributes
     */
    public function testAnchorPopup($expected = '', $uri = '', $title = '', $attributes = false): void
    {
        $uriString = 'http://example.com/';
        $this->createRequest($uriString);

        $this->assertSame($expected, anchor_popup($uri, $title, $attributes, $this->config));
    }

    // Test mailto

    public static function provideMailto(): iterable
    {
        return [
            'page01' => [
                '<a href="mailto:me@my-site.com">Click Here to Contact Me</a>',
                'me@my-site.com',
                'Click Here to Contact Me',
            ],
            'page02' => [
                '<a href="mailto:me@my-site.com" title="Mail me">Contact Me</a>',
                'me@my-site.com',
                'Contact Me',
                ['title' => 'Mail me'],
            ],
            'page03' => [
                '<a href="mailto:me@my-site.com">me@my-site.com</a>',
                'me@my-site.com',
            ],
        ];
    }

    /**
     * @dataProvider provideMailto
     *
     * @param mixed $expected
     * @param mixed $email
     * @param mixed $title
     * @param mixed $attributes
     */
    public function testMailto($expected = '', $email = '', $title = '', $attributes = ''): void
    {
        $uriString = 'http://example.com/';
        $this->createRequest($uriString);

        $this->assertSame($expected, mailto($email, $title, $attributes));
    }

    // Test safe_mailto

    public static function provideSafeMailto(): iterable
    {
        return [
            'page01' => [
                "<script>var l=new Array();l[0] = '>';l[1] = 'a';l[2] = '/';l[3] = '<';l[4] = '|101';l[5] = '|77';l[6] = '|32';l[7] = '|116';l[8] = '|99';l[9] = '|97';l[10] = '|116';l[11] = '|110';l[12] = '|111';l[13] = '|67';l[14] = '|32';l[15] = '|111';l[16] = '|116';l[17] = '|32';l[18] = '|101';l[19] = '|114';l[20] = '|101';l[21] = '|72';l[22] = '|32';l[23] = '|107';l[24] = '|99';l[25] = '|105';l[26] = '|108';l[27] = '|67';l[28] = '>';l[29] = '\"';l[30] = '|109';l[31] = '|111';l[32] = '|99';l[33] = '|46';l[34] = '|101';l[35] = '|116';l[36] = '|105';l[37] = '|115';l[38] = '|45';l[39] = '|121';l[40] = '|109';l[41] = '|64';l[42] = '|101';l[43] = '|109';l[44] = ':';l[45] = 'o';l[46] = 't';l[47] = 'l';l[48] = 'i';l[49] = 'a';l[50] = 'm';l[51] = '\"';l[52] = '=';l[53] = 'f';l[54] = 'e';l[55] = 'r';l[56] = 'h';l[57] = ' ';l[58] = 'a';l[59] = '<';for (var i = l.length-1; i >= 0; i=i-1) {if (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");else document.write(unescape(l[i]));}</script>",
                'me@my-site.com',
                'Click Here to Contact Me',
            ],
            'page02' => [
                "<script>var l=new Array();l[0] = '>';l[1] = 'a';l[2] = '/';l[3] = '<';l[4] = '|101';l[5] = '|77';l[6] = '|32';l[7] = '|116';l[8] = '|99';l[9] = '|97';l[10] = '|116';l[11] = '|110';l[12] = '|111';l[13] = '|67';l[14] = '>';l[15] = '\"';l[16] = '|101';l[17] = '|109';l[18] = '|32';l[19] = '|108';l[20] = '|105';l[21] = '|97';l[22] = '|77';l[23] = ' title=\"';l[24] = '\"';l[25] = '|109';l[26] = '|111';l[27] = '|99';l[28] = '|46';l[29] = '|101';l[30] = '|116';l[31] = '|105';l[32] = '|115';l[33] = '|45';l[34] = '|121';l[35] = '|109';l[36] = '|64';l[37] = '|101';l[38] = '|109';l[39] = ':';l[40] = 'o';l[41] = 't';l[42] = 'l';l[43] = 'i';l[44] = 'a';l[45] = 'm';l[46] = '\"';l[47] = '=';l[48] = 'f';l[49] = 'e';l[50] = 'r';l[51] = 'h';l[52] = ' ';l[53] = 'a';l[54] = '<';for (var i = l.length-1; i >= 0; i=i-1) {if (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");else document.write(unescape(l[i]));}</script>",
                'me@my-site.com',
                'Contact Me',
                ['title' => 'Mail me'],
            ],
            'page03' => [
                "<script>var l=new Array();l[0] = '>';l[1] = 'a';l[2] = '/';l[3] = '<';l[4] = '|109';l[5] = '|111';l[6] = '|99';l[7] = '|46';l[8] = '|101';l[9] = '|116';l[10] = '|105';l[11] = '|115';l[12] = '|45';l[13] = '|121';l[14] = '|109';l[15] = '|64';l[16] = '|101';l[17] = '|109';l[18] = '>';l[19] = '\"';l[20] = '|109';l[21] = '|111';l[22] = '|99';l[23] = '|46';l[24] = '|101';l[25] = '|116';l[26] = '|105';l[27] = '|115';l[28] = '|45';l[29] = '|121';l[30] = '|109';l[31] = '|64';l[32] = '|101';l[33] = '|109';l[34] = ':';l[35] = 'o';l[36] = 't';l[37] = 'l';l[38] = 'i';l[39] = 'a';l[40] = 'm';l[41] = '\"';l[42] = '=';l[43] = 'f';l[44] = 'e';l[45] = 'r';l[46] = 'h';l[47] = ' ';l[48] = 'a';l[49] = '<';for (var i = l.length-1; i >= 0; i=i-1) {if (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");else document.write(unescape(l[i]));}</script>",
                'me@my-site.com',
            ],
        ];
    }

    /**
     * @dataProvider provideSafeMailto
     *
     * @param mixed $expected
     * @param mixed $email
     * @param mixed $title
     * @param mixed $attributes
     */
    public function testSafeMailto($expected = '', $email = '', $title = '', $attributes = ''): void
    {
        $uriString = 'http://example.com/';
        $this->createRequest($uriString);

        $this->assertSame($expected, safe_mailto($email, $title, $attributes));
    }

    public function testSafeMailtoWithCsp(): void
    {
        $this->config->CSPEnabled = true;
        Factories::injectMock('config', 'App', $this->config);

        $html = safe_mailto('foo@example.jp', 'Foo');

        $this->assertMatchesRegularExpression('/<script .*?nonce="\w+?".*?>/u', $html);
    }

    // Test auto_link

    public static function provideAutoLinkUrl(): iterable
    {
        return [
            'test01' => [
                'www.codeigniter.com test',
                '<a href="http://www.codeigniter.com">www.codeigniter.com</a> test',
            ],
            'test02' => [
                'This is my noreply@codeigniter.com test',
                'This is my noreply@codeigniter.com test',
            ],
            'test03' => [
                '<br>www.google.com',
                '<br><a href="http://www.google.com">www.google.com</a>',
            ],
            'test04' => [
                'Download CodeIgniter at www.codeigniter.com. Period test.',
                'Download CodeIgniter at <a href="http://www.codeigniter.com">www.codeigniter.com</a>. Period test.',
            ],
            'test05' => [
                'Download CodeIgniter at www.codeigniter.com, comma test',
                'Download CodeIgniter at <a href="http://www.codeigniter.com">www.codeigniter.com</a>, comma test',
            ],
            'test06' => [
                'This one: ://codeigniter.com must not break this one: http://codeigniter.com',
                'This one: <a href="://codeigniter.com">://codeigniter.com</a> must not break this one: <a href="http://codeigniter.com">http://codeigniter.com</a>',
            ],
            'test07' => [
                'Visit example.com or email foo@bar.com',
                'Visit example.com or email foo@bar.com',
            ],
            'test08' => [
                'Visit www.example.com or email foo@bar.com',
                'Visit <a href="http://www.example.com">www.example.com</a> or email foo@bar.com',
            ],
        ];
    }

    /**
     * @dataProvider provideAutoLinkUrl
     *
     * @param mixed $in
     * @param mixed $out
     */
    public function testAutoLinkUrl($in, $out): void
    {
        $this->assertSame($out, auto_link($in, 'url'));
    }

    public static function provideAutoLinkEmail(): iterable
    {
        return [
            'test01' => [
                'www.codeigniter.com test',
                'www.codeigniter.com test',
            ],
            'test02' => [
                'This is my noreply@codeigniter.com test',
                "This is my <script>var l=new Array();l[0] = '>';l[1] = 'a';l[2] = '/';l[3] = '<';l[4] = '|109';l[5] = '|111';l[6] = '|99';l[7] = '|46';l[8] = '|114';l[9] = '|101';l[10] = '|116';l[11] = '|105';l[12] = '|110';l[13] = '|103';l[14] = '|105';l[15] = '|101';l[16] = '|100';l[17] = '|111';l[18] = '|99';l[19] = '|64';l[20] = '|121';l[21] = '|108';l[22] = '|112';l[23] = '|101';l[24] = '|114';l[25] = '|111';l[26] = '|110';l[27] = '>';l[28] = '\"';l[29] = '|109';l[30] = '|111';l[31] = '|99';l[32] = '|46';l[33] = '|114';l[34] = '|101';l[35] = '|116';l[36] = '|105';l[37] = '|110';l[38] = '|103';l[39] = '|105';l[40] = '|101';l[41] = '|100';l[42] = '|111';l[43] = '|99';l[44] = '|64';l[45] = '|121';l[46] = '|108';l[47] = '|112';l[48] = '|101';l[49] = '|114';l[50] = '|111';l[51] = '|110';l[52] = ':';l[53] = 'o';l[54] = 't';l[55] = 'l';l[56] = 'i';l[57] = 'a';l[58] = 'm';l[59] = '\"';l[60] = '=';l[61] = 'f';l[62] = 'e';l[63] = 'r';l[64] = 'h';l[65] = ' ';l[66] = 'a';l[67] = '<';for (var i = l.length-1; i >= 0; i=i-1) {if (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");else document.write(unescape(l[i]));}</script> test",
            ],
            'test03' => [
                '<br>www.google.com',
                '<br>www.google.com',
            ],
            'test04' => [
                'Download CodeIgniter at www.codeigniter.com. Period test.',
                'Download CodeIgniter at www.codeigniter.com. Period test.',
            ],
            'test05' => [
                'Download CodeIgniter at www.codeigniter.com, comma test',
                'Download CodeIgniter at www.codeigniter.com, comma test',
            ],
            'test06' => [
                'This one: ://codeigniter.com must not break this one: http://codeigniter.com',
                'This one: ://codeigniter.com must not break this one: http://codeigniter.com',
            ],
            'test07' => [
                'Visit example.com or email foo@bar.com',
                "Visit example.com or email <script>var l=new Array();l[0] = '>';l[1] = 'a';l[2] = '/';l[3] = '<';l[4] = '|109';l[5] = '|111';l[6] = '|99';l[7] = '|46';l[8] = '|114';l[9] = '|97';l[10] = '|98';l[11] = '|64';l[12] = '|111';l[13] = '|111';l[14] = '|102';l[15] = '>';l[16] = '\"';l[17] = '|109';l[18] = '|111';l[19] = '|99';l[20] = '|46';l[21] = '|114';l[22] = '|97';l[23] = '|98';l[24] = '|64';l[25] = '|111';l[26] = '|111';l[27] = '|102';l[28] = ':';l[29] = 'o';l[30] = 't';l[31] = 'l';l[32] = 'i';l[33] = 'a';l[34] = 'm';l[35] = '\"';l[36] = '=';l[37] = 'f';l[38] = 'e';l[39] = 'r';l[40] = 'h';l[41] = ' ';l[42] = 'a';l[43] = '<';for (var i = l.length-1; i >= 0; i=i-1) {if (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");else document.write(unescape(l[i]));}</script>",
            ],
            'test08' => [
                'Visit www.example.com or email foo@bar.com',
                "Visit www.example.com or email <script>var l=new Array();l[0] = '>';l[1] = 'a';l[2] = '/';l[3] = '<';l[4] = '|109';l[5] = '|111';l[6] = '|99';l[7] = '|46';l[8] = '|114';l[9] = '|97';l[10] = '|98';l[11] = '|64';l[12] = '|111';l[13] = '|111';l[14] = '|102';l[15] = '>';l[16] = '\"';l[17] = '|109';l[18] = '|111';l[19] = '|99';l[20] = '|46';l[21] = '|114';l[22] = '|97';l[23] = '|98';l[24] = '|64';l[25] = '|111';l[26] = '|111';l[27] = '|102';l[28] = ':';l[29] = 'o';l[30] = 't';l[31] = 'l';l[32] = 'i';l[33] = 'a';l[34] = 'm';l[35] = '\"';l[36] = '=';l[37] = 'f';l[38] = 'e';l[39] = 'r';l[40] = 'h';l[41] = ' ';l[42] = 'a';l[43] = '<';for (var i = l.length-1; i >= 0; i=i-1) {if (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");else document.write(unescape(l[i]));}</script>",
            ],
        ];
    }

    /**
     * @dataProvider provideAutoLinkEmail
     *
     * @param mixed $in
     * @param mixed $out
     */
    public function testAutoLinkEmail($in, $out): void
    {
        $this->assertSame($out, auto_link($in, 'email'));
    }

    public static function provideAutolinkBoth(): iterable
    {
        return [
            'test01' => [
                'www.codeigniter.com test',
                '<a href="http://www.codeigniter.com">www.codeigniter.com</a> test',
            ],
            'test02' => [
                'This is my noreply@codeigniter.com test',
                "This is my <script>var l=new Array();l[0] = '>';l[1] = 'a';l[2] = '/';l[3] = '<';l[4] = '|109';l[5] = '|111';l[6] = '|99';l[7] = '|46';l[8] = '|114';l[9] = '|101';l[10] = '|116';l[11] = '|105';l[12] = '|110';l[13] = '|103';l[14] = '|105';l[15] = '|101';l[16] = '|100';l[17] = '|111';l[18] = '|99';l[19] = '|64';l[20] = '|121';l[21] = '|108';l[22] = '|112';l[23] = '|101';l[24] = '|114';l[25] = '|111';l[26] = '|110';l[27] = '>';l[28] = '\"';l[29] = '|109';l[30] = '|111';l[31] = '|99';l[32] = '|46';l[33] = '|114';l[34] = '|101';l[35] = '|116';l[36] = '|105';l[37] = '|110';l[38] = '|103';l[39] = '|105';l[40] = '|101';l[41] = '|100';l[42] = '|111';l[43] = '|99';l[44] = '|64';l[45] = '|121';l[46] = '|108';l[47] = '|112';l[48] = '|101';l[49] = '|114';l[50] = '|111';l[51] = '|110';l[52] = ':';l[53] = 'o';l[54] = 't';l[55] = 'l';l[56] = 'i';l[57] = 'a';l[58] = 'm';l[59] = '\"';l[60] = '=';l[61] = 'f';l[62] = 'e';l[63] = 'r';l[64] = 'h';l[65] = ' ';l[66] = 'a';l[67] = '<';for (var i = l.length-1; i >= 0; i=i-1) {if (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");else document.write(unescape(l[i]));}</script> test",
            ],
            'test03' => [
                '<br>www.google.com',
                '<br><a href="http://www.google.com">www.google.com</a>',
            ],
            'test04' => [
                'Download CodeIgniter at www.codeigniter.com. Period test.',
                'Download CodeIgniter at <a href="http://www.codeigniter.com">www.codeigniter.com</a>. Period test.',
            ],
            'test05' => [
                'Download CodeIgniter at www.codeigniter.com, comma test',
                'Download CodeIgniter at <a href="http://www.codeigniter.com">www.codeigniter.com</a>, comma test',
            ],
            'test06' => [
                'This one: ://codeigniter.com must not break this one: http://codeigniter.com',
                'This one: <a href="://codeigniter.com">://codeigniter.com</a> must not break this one: <a href="http://codeigniter.com">http://codeigniter.com</a>',
            ],
            'test07' => [
                'Visit example.com or email foo@bar.com',
                "Visit example.com or email <script>var l=new Array();l[0] = '>';l[1] = 'a';l[2] = '/';l[3] = '<';l[4] = '|109';l[5] = '|111';l[6] = '|99';l[7] = '|46';l[8] = '|114';l[9] = '|97';l[10] = '|98';l[11] = '|64';l[12] = '|111';l[13] = '|111';l[14] = '|102';l[15] = '>';l[16] = '\"';l[17] = '|109';l[18] = '|111';l[19] = '|99';l[20] = '|46';l[21] = '|114';l[22] = '|97';l[23] = '|98';l[24] = '|64';l[25] = '|111';l[26] = '|111';l[27] = '|102';l[28] = ':';l[29] = 'o';l[30] = 't';l[31] = 'l';l[32] = 'i';l[33] = 'a';l[34] = 'm';l[35] = '\"';l[36] = '=';l[37] = 'f';l[38] = 'e';l[39] = 'r';l[40] = 'h';l[41] = ' ';l[42] = 'a';l[43] = '<';for (var i = l.length-1; i >= 0; i=i-1) {if (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");else document.write(unescape(l[i]));}</script>",
            ],
            'test08' => [
                'Visit www.example.com or email foo@bar.com',
                "Visit <a href=\"http://www.example.com\">www.example.com</a> or email <script>var l=new Array();l[0] = '>';l[1] = 'a';l[2] = '/';l[3] = '<';l[4] = '|109';l[5] = '|111';l[6] = '|99';l[7] = '|46';l[8] = '|114';l[9] = '|97';l[10] = '|98';l[11] = '|64';l[12] = '|111';l[13] = '|111';l[14] = '|102';l[15] = '>';l[16] = '\"';l[17] = '|109';l[18] = '|111';l[19] = '|99';l[20] = '|46';l[21] = '|114';l[22] = '|97';l[23] = '|98';l[24] = '|64';l[25] = '|111';l[26] = '|111';l[27] = '|102';l[28] = ':';l[29] = 'o';l[30] = 't';l[31] = 'l';l[32] = 'i';l[33] = 'a';l[34] = 'm';l[35] = '\"';l[36] = '=';l[37] = 'f';l[38] = 'e';l[39] = 'r';l[40] = 'h';l[41] = ' ';l[42] = 'a';l[43] = '<';for (var i = l.length-1; i >= 0; i=i-1) {if (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");else document.write(unescape(l[i]));}</script>",
            ],
        ];
    }

    /**
     * @dataProvider provideAutolinkBoth
     *
     * @param mixed $in
     * @param mixed $out
     */
    public function testAutolinkBoth($in, $out): void
    {
        $this->assertSame($out, auto_link($in));
    }

    public static function provideAutoLinkPopup(): iterable
    {
        return [
            'test01' => [
                'www.codeigniter.com test',
                '<a href="http://www.codeigniter.com" target="_blank">www.codeigniter.com</a> test',
            ],
            'test02' => [
                'This is my noreply@codeigniter.com test',
                'This is my noreply@codeigniter.com test',
            ],
            'test03' => [
                '<br>www.google.com',
                '<br><a href="http://www.google.com" target="_blank">www.google.com</a>',
            ],
            'test04' => [
                'Download CodeIgniter at www.codeigniter.com. Period test.',
                'Download CodeIgniter at <a href="http://www.codeigniter.com" target="_blank">www.codeigniter.com</a>. Period test.',
            ],
            'test05' => [
                'Download CodeIgniter at www.codeigniter.com, comma test',
                'Download CodeIgniter at <a href="http://www.codeigniter.com" target="_blank">www.codeigniter.com</a>, comma test',
            ],
            'test06' => [
                'This one: ://codeigniter.com must not break this one: http://codeigniter.com',
                'This one: <a href="://codeigniter.com" target="_blank">://codeigniter.com</a> must not break this one: <a href="http://codeigniter.com" target="_blank">http://codeigniter.com</a>',
            ],
            'test07' => [
                'Visit example.com or email foo@bar.com',
                'Visit example.com or email foo@bar.com',
            ],
            'test08' => [
                'Visit www.example.com or email foo@bar.com',
                'Visit <a href="http://www.example.com" target="_blank">www.example.com</a> or email foo@bar.com',
            ],
        ];
    }

    /**
     * @dataProvider provideAutoLinkPopup
     *
     * @param mixed $in
     * @param mixed $out
     */
    public function testAutoLinkPopup($in, $out): void
    {
        $this->assertSame($out, auto_link($in, 'url', true));
    }

    // Test prep_url

    public static function providePrepUrl(): iterable
    {
        // input, expected, secure
        return [
            [
                '',
                '',
                false,
            ],
            [
                '//',
                '',
                false,
            ],
            [
                '//codeigniter.com',
                'http://codeigniter.com',
                false,
            ],
            [
                'codeigniter.com',
                'http://codeigniter.com',
                false,
            ],
            [
                'www.codeigniter.com',
                'http://www.codeigniter.com',
                false,
            ],
            [
                'http://www.codeigniter.com',
                'http://www.codeigniter.com',
                false,
            ],
            [
                'https://www.codeigniter.com',
                'https://www.codeigniter.com',
                false,
            ],
            [
                '',
                '',
                true,
            ],
            [
                '//',
                '',
                true,
            ],
            [
                '//codeigniter.com',
                'https://codeigniter.com',
                true,
            ],
            [
                'codeigniter.com',
                'https://codeigniter.com',
                true,
            ],
            [
                'www.codeigniter.com',
                'https://www.codeigniter.com',
                true,
            ],
            [
                'http://www.codeigniter.com',
                'https://www.codeigniter.com',
                true,
            ],
            [
                'https://www.codeigniter.com',
                'https://www.codeigniter.com',
                true,
            ],
        ];
    }

    /**
     * @dataProvider providePrepUrl
     */
    public function testPrepUrl(string $input, string $expected, bool $secure): void
    {
        $this->assertSame($expected, prep_url($input, $secure));
    }

    // Test url_title

    public function testUrlTitle(): void
    {
        $words = [
            'foo bar /'       => 'foo-bar',
            '\  testing 12'   => 'testing-12',
            'Éléphant de PHP' => 'éléphant-de-php',
        ];

        foreach ($words as $in => $out) {
            $this->assertSame($out, url_title($in, '-', true));
        }
    }

    public function testUrlTitleExtraDashes(): void
    {
        $words = [
            '_foo bar_'                 => 'foo_bar',
            '_What\'s wrong with CSS?_' => 'Whats_wrong_with_CSS',
            'Éléphant de PHP'           => 'Éléphant_de_PHP',
        ];

        foreach ($words as $in => $out) {
            $this->assertSame($out, url_title($in, '_'));
        }
    }

    // Test mb_url_title

    public function testMbUrlTitle(): void
    {
        $words = [
            'foo bar /'       => 'foo-bar',
            '\  testing 12'   => 'testing-12',
            'Éléphant de PHP' => 'elephant-de-php',
            'ä ö ü Ĝ β ę'     => 'ae-oe-ue-g-v-e',
        ];

        foreach ($words as $in => $out) {
            $this->assertSame($out, mb_url_title($in, '-', true));
        }
    }

    public function testMbUrlTitleExtraDashes(): void
    {
        $words = [
            '_foo bar_'                 => 'foo_bar',
            '_What\'s wrong with CSS?_' => 'Whats_wrong_with_CSS',
            'Éléphant de PHP'           => 'Elephant_de_PHP',
            'ä ö ü Ĝ β ę'               => 'ae_oe_ue_G_v_e',
        ];

        foreach ($words as $in => $out) {
            $this->assertSame($out, mb_url_title($in, '_'));
        }
    }

    /**
     * @dataProvider provideUrlTo
     */
    public function testUrlTo(string $expected, string $input, ...$args): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com';

        $routes = service('routes');
        // @TODO Do not put any placeholder after (:any).
        //       Because the number of parameters passed to the controller method may change.
        $routes->add('path/(:any)/to/(:num)', 'myController::goto/$1/$2', ['as' => 'gotoPage']);
        $routes->add('route/(:any)/to/(:num)', 'myOtherController::goto/$1/$2');

        $this->assertSame($expected, url_to($input, ...$args));
    }

    /**
     * @dataProvider provideUrlToThrowsOnEmptyOrMissingRoute
     */
    public function testUrlToThrowsOnEmptyOrMissingRoute(string $route): void
    {
        $this->expectException(RouterException::class);

        url_to($route);
    }

    public static function provideUrlTo(): iterable
    {
        $page = config('App')->indexPage !== '' ? config('App')->indexPage . '/' : '';

        return [
            [
                "http://example.com/{$page}path/string/to/13",
                'gotoPage',
                'string',
                13,
            ],
            [
                "http://example.com/{$page}route/string/to/13",
                'myOtherController::goto',
                'string',
                13,
            ],
        ];
    }

    public static function provideUrlToThrowsOnEmptyOrMissingRoute(): iterable
    {
        return [
            [
                '',
            ],
            [
                'Nope::doesNotExist',
            ],
        ];
    }

    public function testUrlToWithSupportedLocaleInRoute(): void
    {
        Services::createRequest(new App());
        $routes = service('routes');
        $routes->add(
            '{locale}/path/(:segment)/to/(:num)',
            'myController::goto/$1/$2',
            ['as' => 'path-to']
        );

        $this->assertSame(
            'http://example.com/index.php/en/path/string/to/13',
            url_to('path-to', 'string', 13, 'en')
        );
    }

    public function testUrlToWithNamedRouteWithNestedParentheses(): void
    {
        Services::createRequest(new App());
        $routes = service('routes');

        // The route will be:
        //   docs/(master|\d+\.(?:\d+|x))/([a-z0-9-]+)
        $routes->addPlaceholder([
            'version' => 'master|\d+\.(?:\d+|x)',
            'page'    => '[a-z0-9-]+',
        ]);
        $routes->get('docs/(:version)/(:page)', static function () {
            echo 'Test the documentation segment';
        }, ['as' => 'docs.version']);

        $this->assertSame(
            'http://example.com/index.php/docs/10.9/install',
            url_to('docs.version', '10.9', 'install')
        );
    }

    public function testUrlToWithRouteWithNestedParentheses(): void
    {
        Services::createRequest(new App());
        $routes = service('routes');

        // The route will be:
        //   images/(^.*\.(?:jpg|png)$)
        $routes->addPlaceholder('imgFileExt', '^.*\.(?:jpg|png)$');
        $routes->get('images/(:imgFileExt)', 'Images::getFile/$1');

        $this->assertSame(
            'http://example.com/index.php/images/test.jpg',
            url_to('Images::getFile', 'test.jpg')
        );
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/7651
     */
    public function testUrlToMissingArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing argument for "(:alpha)" in route "(:alpha)/login".');

        $routes = Services::routes();
        $routes->group('(:alpha)', static function ($routes): void {
            $routes->match(['get'], 'login', 'Common\LoginController::loginView', ['as' => 'loginURL']);
        });

        url_to('loginURL');
    }
}
