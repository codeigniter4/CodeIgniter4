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

use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;

/**
 * @backupGlobals enabled
 *
 * @internal
 */
final class URITest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        Factories::reset('config');
    }

    public function testConstructorSetsAllParts()
    {
        $uri = new URI('http://username:password@hostname:9090/path?arg=value#anchor');

        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('username', $uri->getUserInfo());
        $this->assertSame('hostname', $uri->getHost());
        $this->assertSame('/path', $uri->getPath());
        $this->assertSame('arg=value', $uri->getQuery());
        $this->assertSame(9090, $uri->getPort());
        $this->assertSame('anchor', $uri->getFragment());

        // Password ignored by default for security reasons.
        $this->assertSame('username@hostname:9090', $uri->getAuthority());

        $this->assertSame(['path'], $uri->getSegments());
    }

    public function testSegmentsIsPopulatedRightForMultipleSegments()
    {
        $uri = new URI('http://hostname/path/to/script');

        $this->assertSame(['path', 'to', 'script'], $uri->getSegments());
        $this->assertSame('path', $uri->getSegment(1));
        $this->assertSame('to', $uri->getSegment(2));
        $this->assertSame('script', $uri->getSegment(3));
        $this->assertSame('', $uri->getSegment(4));

        $this->assertSame(3, $uri->getTotalSegments());
    }

    public function testSegmentOutOfRange()
    {
        $this->expectException(HTTPException::class);
        $uri = new URI('http://hostname/path/to/script');
        $uri->getSegment(5);
    }

    public function testSegmentOutOfRangeWithSilent()
    {
        $url = 'http://abc.com/a123/b/c';
        $uri = new URI($url);
        $this->assertSame('', $uri->setSilent()->getSegment(22));
    }

    public function testSegmentOutOfRangeWithDefaultValue()
    {
        $this->expectException(HTTPException::class);
        $url = 'http://abc.com/a123/b/c';
        $uri = new URI($url);
        $uri->getSegment(22, 'something');
    }

    public function testSegmentOutOfRangeWithSilentAndDefaultValue()
    {
        $url = 'http://abc.com/a123/b/c';
        $uri = new URI($url);
        $this->assertSame('something', $uri->setSilent()->getSegment(22, 'something'));
    }

    public function testSegmentsWithDefaultValueAndSilent()
    {
        $uri = new URI('http://hostname/path/to');
        $uri->setSilent();

        $this->assertSame(['path', 'to'], $uri->getSegments());
        $this->assertSame('path', $uri->getSegment(1));
        $this->assertSame('to', $uri->getSegment(2, 'different'));
        $this->assertSame('script', $uri->getSegment(3, 'script'));
        $this->assertSame('', $uri->getSegment(3));

        $this->assertSame(2, $uri->getTotalSegments());
    }

    public function testSegmentOutOfRangeWithDefaultValuesAndSilent()
    {
        $uri = new URI('http://hostname/path/to/script');
        $uri->setSilent();

        $this->assertSame('', $uri->getSegment(22));
        $this->assertSame('something', $uri->getSegment(33, 'something'));

        $this->assertSame(3, $uri->getTotalSegments());
        $this->assertSame(['path', 'to', 'script'], $uri->getSegments());
    }

    public function testCanCastAsString()
    {
        $url = 'http://username:password@hostname:9090/path?arg=value#anchor';
        $uri = new URI($url);

        $expected = 'http://username@hostname:9090/path?arg=value#anchor';

        $this->assertSame($expected, (string) $uri);
    }

    public function testSimpleUri()
    {
        $url = 'http://example.com';
        $uri = new URI($url);
        $this->assertSame($url, (string) $uri);

        $url = 'http://example.com/';
        $uri = new URI($url);
        $this->assertSame($url, (string) $uri);
    }

    public function testEmptyUri()
    {
        $url = '';
        $uri = new URI($url);
        $this->assertSame('http://' . $url, (string) $uri);
        $url = '/';
        $uri = new URI($url);
        $this->assertSame('http://', (string) $uri);
    }

    public function testMalformedUri()
    {
        $this->expectException(HTTPException::class);
        $url = 'http://abc:a123';
        $uri = new URI($url);
    }

    public function testMissingScheme()
    {
        $url = 'http://foo.bar/baz';
        $uri = new URI($url);
        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('foo.bar', $uri->getAuthority());
        $this->assertSame('/baz', $uri->getPath());
        $this->assertSame($url, (string) $uri);
    }

    public function testSchemeSub()
    {
        $url = 'example.com';
        $uri = new URI('http://' . $url);
        $uri->setScheme('x');
        $this->assertSame('x://' . $url, (string) $uri);
    }

    public function testSetSchemeSetsValue()
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $expected = 'https://example.com/path';

        $uri->setScheme('https');
        $this->assertSame('https', $uri->getScheme());
        $this->assertSame($expected, (string) $uri);
    }

    public function testSetUserInfoSetsValue()
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $expected = 'http://user@example.com/path';

        $uri->setUserInfo('user', 'password');
        $this->assertSame('user', $uri->getUserInfo());
        $this->assertSame($expected, (string) $uri);
    }

    public function testUserInfoCanShowPassword()
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $expected = 'http://user@example.com/path';

        $uri->setUserInfo('user', 'password');
        $this->assertSame('user', $uri->getUserInfo());
        $this->assertSame($expected, (string) $uri);

        $uri->showPassword();

        $expected = 'http://user:password@example.com/path';

        $this->assertSame('user:password', $uri->getUserInfo());
        $this->assertSame($expected, (string) $uri);
    }

    public function testSetHostSetsValue()
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $expected = 'http://another.com/path';

        $uri->setHost('another.com');
        $this->assertSame('another.com', $uri->getHost());
        $this->assertSame($expected, (string) $uri);
    }

    public function testSetPortSetsValue()
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $expected = 'http://example.com:9000/path';

        $uri->setPort(9000);
        $this->assertSame(9000, $uri->getPort());
        $this->assertSame($expected, (string) $uri);
    }

    public function testSetPortInvalidValues()
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $errorString = lang('HTTP.invalidPort', [70000]);
        $this->assertNotEmpty($errorString);

        $this->expectException(HTTPException::class);
        $this->expectExceptionMessage(lang('HTTP.invalidPort', ['70000']));
        $uri->setPort(70000);
    }

    public function testSetPortInvalidValuesSilent()
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $uri->setSilent()->setPort(70000);

        $this->assertNull($uri->getPort());
    }

    public function testSetPortTooSmall()
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $this->expectException(HTTPException::class);
        $this->expectExceptionMessage(lang('HTTP.invalidPort', [-1]));
        $uri->setPort(-1);
    }

    public function testSetPortZero()
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $this->expectException(HTTPException::class);
        $this->expectExceptionMessage(lang('HTTP.invalidPort', [0]));
        $uri->setPort(0);
    }

    public function testCatchesBadPort()
    {
        $this->expectException(HTTPException::class);
        $url = 'http://username:password@hostname:90909/path?arg=value#anchor';
        $uri = new URI();
        $uri->setURI($url);
    }

    public function testSetPathSetsValue()
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $expected = 'http://example.com/somewhere/else';

        $uri->setPath('somewhere/else');
        $this->assertSame('somewhere/else', $uri->getPath());
        $this->assertSame($expected, (string) $uri);
    }

    public function invalidPaths()
    {
        return [
            'dot-segment' => [
                '/./path/to/nowhere',
                '/path/to/nowhere',
            ],
            'double-dots' => [
                '/../path/to/nowhere',
                '/path/to/nowhere',
            ],
            'start-dot' => [
                './path/to/nowhere',
                '/path/to/nowhere',
            ],
            'start-double' => [
                '../path/to/nowhere',
                '/path/to/nowhere',
            ],
            'decoded' => [
                '../%41path',
                '/Apath',
            ],
            'encoded' => [
                '/path^here',
                '/path%5Ehere',
            ],
        ];
    }

    /**
     * @dataProvider invalidPaths
     *
     * @param mixed $path
     * @param mixed $expected
     */
    public function testPathGetsFiltered($path, $expected)
    {
        $uri = new URI();
        $uri->setPath($path);
        $this->assertSame($expected, $uri->getPath());
    }

    public function testSetFragmentSetsValue()
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $expected = 'http://example.com/path#good-stuff';

        $uri->setFragment('#good-stuff');
        $this->assertSame('good-stuff', $uri->getFragment());
        $this->assertSame($expected, (string) $uri);
    }

    public function testSetQuerySetsValue()
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $expected = 'http://example.com/path?key=value&second_key=value.2';

        $uri->setQuery('?key=value&second.key=value.2');
        $this->assertSame('key=value&second_key=value.2', $uri->getQuery());
        $this->assertSame($expected, (string) $uri);
    }

    public function testSetQuerySetsValueWithUseRawQueryString()
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $expected = 'http://example.com/path?key=value&second.key=value.2';

        $uri->useRawQueryString()->setQuery('?key=value&second.key=value.2');
        $this->assertSame('key=value&second.key=value.2', $uri->getQuery());
        $this->assertSame($expected, (string) $uri);
    }

    public function testSetQueryArraySetsValue()
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $expected = 'http://example.com/path?key=value&second_key=value.2';

        $uri->setQueryArray(['key' => 'value', 'second.key' => 'value.2']);
        $this->assertSame('key=value&second_key=value.2', $uri->getQuery());
        $this->assertSame($expected, (string) $uri);
    }

    public function testSetQueryArraySetsValueWithUseRawQueryString()
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $expected = 'http://example.com/path?key=value&second.key=value.2';

        $uri->useRawQueryString()->setQueryArray(['key' => 'value', 'second.key' => 'value.2']);
        $this->assertSame('key=value&second.key=value.2', $uri->getQuery());
        $this->assertSame($expected, (string) $uri);
    }

    public function testSetQueryThrowsErrorWhenFragmentPresent()
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $this->expectException(HTTPException::class);
        $uri->setQuery('?key=value#fragment');
    }

    public function testSetQueryThrowsErrorWhenFragmentPresentSilent()
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $uri->setSilent()->setQuery('?key=value#fragment');

        $this->assertSame('', $uri->getQuery());
    }

    public function authorityInfo()
    {
        return [
            'host-only' => [
                'http://foo.com/bar',
                'foo.com',
            ],
            'host-port' => [
                'http://foo.com:3000/bar',
                'foo.com:3000',
            ],
            'user-host' => [
                'http://me@foo.com/bar',
                'me@foo.com',
            ],
            'user-host-port' => [
                'http://me@foo.com:3000/bar',
                'me@foo.com:3000',
            ],
        ];
    }

    /**
     * @dataProvider authorityInfo
     *
     * @param mixed $url
     * @param mixed $expected
     */
    public function testAuthorityReturnsExceptedValues($url, $expected)
    {
        $uri = new URI($url);
        $this->assertSame($expected, $uri->getAuthority());
    }

    public function defaultPorts()
    {
        return [
            'http' => [
                'http',
                80,
            ],
            'https' => [
                'https',
                443,
            ],
        ];
    }

    /**
     * @dataProvider defaultPorts
     *
     * @param mixed $scheme
     * @param mixed $port
     */
    public function testAuthorityRemovesDefaultPorts($scheme, $port)
    {
        $url = "{$scheme}://example.com:{$port}/path";
        $uri = new URI($url);

        $expected = "{$scheme}://example.com/path";

        $this->assertSame($expected, (string) $uri);
    }

    public function testSetAuthorityReconstitutes()
    {
        $authority = 'me@foo.com:3000';

        $uri = new URI();
        $uri->setAuthority($authority);

        $this->assertSame($authority, $uri->getAuthority());
    }

    public function defaultDots()
    {
        return [
            [
                '',
                '',
            ],
            [
                '/',
                '/',
            ],
            [
                '.',
                '',
            ],
            [
                '..',
                '',
            ],
            [
                '/.',
                '/',
            ],
            [
                '/..',
                '/',
            ],
            [
                '//',
                '/',
            ],
            [
                '/foo/..',
                '/',
            ],
            [
                '//foo//..',
                '/',
            ],
            [
                '/foo/../..',
                '/',
            ],
            [
                '/foo/../.',
                '/',
            ],
            [
                '/./foo/..',
                '/',
            ],
            [
                '/./foo',
                '/foo',
            ],
            [
                '/./foo/',
                '/foo/',
            ],
            [
                '/./foo/bar/baz/pho/../..',
                '/foo/bar',
            ],
            [
                '*',
                '*',
            ],
            [
                '/foo',
                '/foo',
            ],
            [
                '/abc/123/../foo/',
                '/abc/foo/',
            ],
            [
                '/a/b/c/./../../g',
                '/a/g',
            ],
            [
                '/b/c/./../../g',
                '/g',
            ],
            [
                '/b/c/./../../g',
                '/g',
            ],
            [
                '/c/./../../g',
                '/g',
            ],
            [
                '/./../../g',
                '/g',
            ],
        ];
    }

    /**
     * @dataProvider defaultDots
     *
     * @param mixed $path
     * @param mixed $expected
     */
    public function testRemoveDotSegments($path, $expected)
    {
        $this->assertSame($expected, URI::removeDotSegments($path));
    }

    public function defaultResolutions()
    {
        return [
            [
                'g',
                'http://a/b/c/g',
            ],
            [
                'g/',
                'http://a/b/c/g/',
            ],
            [
                '/g',
                'http://a/g',
            ],
            [
                '#s',
                'http://a/b/c/d#s',
            ],
            [
                'http://abc.com/x',
                'http://abc.com/x',
            ],
            [
                '?fruit=banana',
                'http://a/b/c/d?fruit=banana',
            ],
        ];
    }

    /**
     * @dataProvider defaultResolutions
     *
     * @param mixed $rel
     * @param mixed $expected
     */
    public function testResolveRelativeURI($rel, $expected)
    {
        $base = 'http://a/b/c/d';

        $uri = new URI($base);

        $new = $uri->resolveRelativeURI($rel);

        $this->assertSame($expected, (string) $new);
    }

    /**
     * @dataProvider defaultResolutions
     * @group        single
     *
     * @param mixed $rel
     * @param mixed $expected
     */
    public function testResolveRelativeURIHTTPS($rel, $expected)
    {
        $base = 'https://a/b/c/d';

        $expected = str_replace('http:', 'https:', $expected);

        $uri = new URI($base);

        $new = $uri->resolveRelativeURI($rel);

        $this->assertSame($expected, (string) $new);
    }

    public function testResolveRelativeURIWithNoBase()
    {
        $base = 'http://a';

        $uri = new URI($base);

        $new = $uri->resolveRelativeURI('x');

        $this->assertSame('http://a/x', (string) $new);
    }

    public function testAddQueryVar()
    {
        $base = 'http://example.com/foo';

        $uri = new URI($base);

        $uri->addQuery('bar', 'baz');

        $this->assertSame('http://example.com/foo?bar=baz', (string) $uri);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/pull/954
     */
    public function testSetQueryDecode()
    {
        $base = 'http://example.com/foo';

        $uri     = new URI($base);
        $encoded = urlencode('you+alice+to+the+little');

        $uri->setQuery("q={$encoded}");

        // Should NOT be double-encoded, since http_build_query
        // will encode the value again.
        $this->assertSame("q={$encoded}", $uri->getQuery());
    }

    public function testAddQueryVarRespectsExistingQueryVars()
    {
        $base = 'http://example.com/foo?bar=baz';

        $uri = new URI($base);

        $uri->addQuery('baz', 'foz');

        $this->assertSame('http://example.com/foo?bar=baz&baz=foz', (string) $uri);
    }

    public function testStripQueryVars()
    {
        $base = 'http://example.com/foo?foo=bar&bar=baz&baz=foz';

        $uri = new URI($base);

        $uri->stripQuery('bar', 'baz');

        $this->assertSame('http://example.com/foo?foo=bar', (string) $uri);
    }

    public function testKeepQueryVars()
    {
        $base = 'http://example.com/foo?foo=bar&bar=baz&baz=foz';

        $uri = new URI($base);

        $uri->keepQuery('bar', 'baz');

        $this->assertSame('http://example.com/foo?bar=baz&baz=foz', (string) $uri);
    }

    public function testEmptyQueryVars()
    {
        $base = 'http://example.com/foo';

        $uri = new URI($base);
        $uri->setQuery('foo=&bar=baz&baz=foz');
        $this->assertSame('http://example.com/foo?foo=&bar=baz&baz=foz', (string) $uri);
    }

    public function testGetQueryExcept()
    {
        $base = 'http://example.com/foo?foo=bar&bar=baz&baz=foz';

        $uri = new URI($base);

        $this->assertSame('foo=bar&baz=foz', $uri->getQuery(['except' => ['bar']]));
    }

    public function testGetQueryOnly()
    {
        $base = 'http://example.com/foo?foo=bar&bar=baz&baz=foz';

        $uri = new URI($base);

        $this->assertSame('bar=baz', $uri->getQuery(['only' => ['bar']]));
        $this->assertSame('foo=bar&baz=foz', $uri->getQuery(['except' => 'bar']));
    }

    public function testGetQueryWithStrings()
    {
        $base = 'http://example.com/foo?foo=bar&bar=baz&baz=foz';

        $uri = new URI($base);

        $this->assertSame('bar=baz', $uri->getQuery(['only' => 'bar']));
    }

    /**
     * @see   https://github.com/codeigniter4/CodeIgniter4/issues/331
     * @group single
     */
    public function testNoExtraSlashes()
    {
        $this->assertSame('http://entirely.different.com/subfolder', (string) (new URI('entirely.different.com/subfolder')));
        $this->assertSame('http://localhost/subfolder', (string) (new URI('localhost/subfolder')));
        $this->assertSame('http://localtest.me/subfolder', (string) (new URI('localtest.me/subfolder')));
    }

    public function testSetSegment()
    {
        $base = 'http://example.com/foo/bar/baz';

        $uri = new URI($base);
        $uri->setSegment(2, 'banana');

        $this->assertSame('foo/banana/baz', $uri->getPath());
    }

    public function testSetSegmentFallback()
    {
        $base = 'http://example.com';

        $uri = new URI($base);
        $uri->setSegment(1, 'first');
        $uri->setSegment(3, 'third');

        $this->assertSame('first/third', $uri->getPath());

        $uri->setSegment(2, 'second');

        $this->assertSame('first/second', $uri->getPath());

        $uri->setSegment(3, 'third');

        $this->assertSame('first/second/third', $uri->getPath());

        $uri->setSegment(5, 'fifth');

        $this->assertSame('first/second/third/fifth', $uri->getPath());

        // sixth or seventh was not set
        $this->expectException(HTTPException::class);

        $uri->setSegment(8, 'eighth');
    }

    public function testSetBadSegment()
    {
        $this->expectException(HTTPException::class);
        $base = 'http://example.com/foo/bar/baz';

        $uri = new URI($base);
        $uri->setSegment(6, 'banana');
    }

    public function testSetBadSegmentSilent()
    {
        $base = 'http://example.com/foo/bar/baz';

        $uri = new URI($base);

        $segments = $uri->getSegments();
        $uri->setSilent()->setSegment(6, 'banana');

        $this->assertSame($segments, $uri->getSegments());
    }

    // Exploratory testing, investigating https://github.com/codeigniter4/CodeIgniter4/issues/2016

    public function testBasedNoIndex()
    {
        $this->resetServices();

        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/ci/v4/controller/method';

        $config            = new App();
        $config->baseURL   = 'http://example.com/ci/v4';
        $config->indexPage = 'index.php';
        $request           = Services::request($config);
        $request->uri      = new URI('http://example.com/ci/v4/controller/method');

        Services::injectMock('request', $request);

        // going through request
        $this->assertSame('http://example.com/ci/v4/controller/method', (string) $request->uri);
        $this->assertSame('/ci/v4/controller/method', $request->getUri()->getPath());

        // standalone
        $uri = new URI('http://example.com/ci/v4/controller/method');
        $this->assertSame('http://example.com/ci/v4/controller/method', (string) $uri);
        $this->assertSame('/ci/v4/controller/method', $uri->getPath());

        $this->assertSame($uri->getPath(), $request->getUri()->getPath());
    }

    public function testBasedWithIndex()
    {
        $this->resetServices();

        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/ci/v4/index.php/controller/method';

        $config            = new App();
        $config->baseURL   = 'http://example.com/ci/v4';
        $config->indexPage = 'index.php';
        $request           = Services::request($config);
        $request->uri      = new URI('http://example.com/ci/v4/index.php/controller/method');

        Services::injectMock('request', $request);

        // going through request
        $this->assertSame('http://example.com/ci/v4/index.php/controller/method', (string) $request->getUri());
        $this->assertSame('/ci/v4/index.php/controller/method', $request->getUri()->getPath());

        // standalone
        $uri = new URI('http://example.com/ci/v4/index.php/controller/method');
        $this->assertSame('http://example.com/ci/v4/index.php/controller/method', (string) $uri);
        $this->assertSame('/ci/v4/index.php/controller/method', $uri->getPath());

        $this->assertSame($uri->getPath(), $request->getUri()->getPath());
    }

    public function testForceGlobalSecureRequests()
    {
        $this->resetServices();

        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/ci/v4/controller/method';

        $config                            = new App();
        $config->baseURL                   = 'http://example.com/ci/v4';
        $config->indexPage                 = 'index.php';
        $config->forceGlobalSecureRequests = true;

        Factories::injectMock('config', 'App', $config);

        $uri     = new URI('http://example.com/ci/v4/controller/method');
        $request = new IncomingRequest($config, $uri, 'php://input', new UserAgent());

        Services::injectMock('request', $request);

        // Detected by request
        $this->assertSame('https://example.com/ci/v4/controller/method', (string) $request->getUri());

        // Standalone
        $uri = new URI('http://example.com/ci/v4/controller/method');
        $this->assertSame('https://example.com/ci/v4/controller/method', (string) $uri);

        $this->assertSame(trim($uri->getPath(), '/'), trim($request->getUri()->getPath(), '/'));
    }

    public function testZeroAsURIPath()
    {
        $url = 'http://example.com/0';
        $uri = new URI($url);
        $this->assertSame($url, (string) $uri);
        $this->assertSame('/0', $uri->getPath());
    }

    public function testEmptyURIPath()
    {
        $url = 'http://example.com/';
        $uri = new URI($url);
        $this->assertSame([], $uri->getSegments());
        $this->assertSame(0, $uri->getTotalSegments());
    }

    public function testSetURI()
    {
        $url = ':';
        $uri = new URI();

        $this->expectException(HTTPException::class);
        $this->expectExceptionMessage(lang('HTTP.cannotParseURI', [$url]));

        $uri->setURI($url);
    }

    public function testSetURISilent()
    {
        $url = ':';
        $uri = new URI();
        $uri->setSilent()->setURI($url);

        $this->assertTrue(true);
    }

    public function testCreateURIString()
    {
        $expected = 'https://example.com/';
        $uri      = URI::createURIString('https', 'example.com/', '/');

        $this->assertSame($expected, $uri);
    }
}
