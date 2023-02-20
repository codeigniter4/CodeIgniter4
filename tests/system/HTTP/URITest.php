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
 *
 * @group Others
 */
final class URITest extends CIUnitTestCase
{
    public function testConstructorSetsAllParts(): void
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

    public function testSegmentsIsPopulatedRightForMultipleSegments(): void
    {
        $uri = new URI('http://hostname/path/to/script');

        $this->assertSame(['path', 'to', 'script'], $uri->getSegments());
        $this->assertSame('path', $uri->getSegment(1));
        $this->assertSame('to', $uri->getSegment(2));
        $this->assertSame('script', $uri->getSegment(3));
        $this->assertSame('', $uri->getSegment(4));

        $this->assertSame(3, $uri->getTotalSegments());
    }

    public function testSegmentOutOfRange(): void
    {
        $this->expectException(HTTPException::class);

        $uri = new URI('http://hostname/path/to/script');
        $uri->getSegment(5);
    }

    public function testSegmentOutOfRangeWithSilent(): void
    {
        $url = 'http://abc.com/a123/b/c';
        $uri = new URI($url);

        $this->assertSame('', $uri->setSilent()->getSegment(22));
    }

    public function testSegmentOutOfRangeWithDefaultValue(): void
    {
        $this->expectException(HTTPException::class);

        $url = 'http://abc.com/a123/b/c';
        $uri = new URI($url);
        $uri->getSegment(22, 'something');
    }

    public function testSegmentOutOfRangeWithSilentAndDefaultValue(): void
    {
        $url = 'http://abc.com/a123/b/c';
        $uri = new URI($url);

        $this->assertSame('something', $uri->setSilent()->getSegment(22, 'something'));
    }

    public function testSegmentsWithDefaultValueAndSilent(): void
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

    public function testSegmentOutOfRangeWithDefaultValuesAndSilent(): void
    {
        $uri = new URI('http://hostname/path/to/script');
        $uri->setSilent();

        $this->assertSame('', $uri->getSegment(22));
        $this->assertSame('something', $uri->getSegment(33, 'something'));

        $this->assertSame(3, $uri->getTotalSegments());
        $this->assertSame(['path', 'to', 'script'], $uri->getSegments());
    }

    public function testCanCastAsString(): void
    {
        $url = 'http://username:password@hostname:9090/path?arg=value#anchor';
        $uri = new URI($url);

        $expected = 'http://username@hostname:9090/path?arg=value#anchor';
        $this->assertSame($expected, (string) $uri);
    }

    /**
     * @dataProvider provideSimpleUri
     */
    public function testSimpleUri(string $url, string $expectedURL, string $expectedPath): void
    {
        $uri = new URI($url);

        $this->assertSame($expectedURL, (string) $uri);
        $this->assertSame($expectedPath, $uri->getPath());
    }

    public static function provideSimpleUri(): iterable
    {
        return [
            '' => [
                'http://example.com', // url
                'http://example.com', // expectedURL
                '',                   // expectedPath
            ],
            '/' => [
                'http://example.com/',
                'http://example.com/',
                '/',
            ],
            '/one/two' => [
                'http://example.com/one/two',
                'http://example.com/one/two',
                '/one/two',
            ],
            '/one/two/' => [
                'http://example.com/one/two/',
                'http://example.com/one/two/',
                '/one/two/',
            ],
            '/one/two//' => [
                'http://example.com/one/two//',
                'http://example.com/one/two/',
                '/one/two/',
            ],
            '//one/two//' => [
                'http://example.com//one/two//',
                'http://example.com/one/two/',
                '/one/two/',
            ],
            '//one//two//' => [
                'http://example.com//one//two//',
                'http://example.com/one/two/',
                '/one/two/',
            ],
            '///one/two' => [
                'http://example.com///one/two', // url
                'http://example.com/one/two',   // expectedURL
                '/one/two',                     // expectedPath
            ],
            '/one/two///' => [
                'http://example.com/one/two///',
                'http://example.com/one/two/',
                '/one/two/',
            ],
        ];
    }

    public function testEmptyUri(): void
    {
        $url = '';
        $uri = new URI($url);

        $this->assertSame('http://', (string) $uri);

        $url = '/';
        $uri = new URI($url);

        $this->assertSame('http://', (string) $uri);
    }

    public function testMalformedUri(): void
    {
        $this->expectException(HTTPException::class);

        $url = 'http://abc:a123';
        new URI($url);
    }

    public function testMissingScheme(): void
    {
        $url = 'http://foo.bar/baz';
        $uri = new URI($url);

        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('foo.bar', $uri->getAuthority());
        $this->assertSame('/baz', $uri->getPath());
        $this->assertSame($url, (string) $uri);
    }

    public function testSchemeSub(): void
    {
        $url = 'example.com';
        $uri = new URI('http://' . $url);
        $uri->setScheme('x');

        $this->assertSame('x://' . $url, (string) $uri);
    }

    public function testSetSchemeSetsValue(): void
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $uri->setScheme('https');

        $this->assertSame('https', $uri->getScheme());
        $expected = 'https://example.com/path';
        $this->assertSame($expected, (string) $uri);
    }

    public function testWithScheme()
    {
        $url = 'example.com';
        $uri = new URI('http://' . $url);

        $new = $uri->withScheme('x');

        $this->assertSame('x://' . $url, (string) $new);
        $this->assertSame('http://' . $url, (string) $uri);
    }

    public function testWithSchemeSetsHttps()
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $new = $uri->withScheme('https');

        $this->assertSame('https', $new->getScheme());
        $this->assertSame('http', $uri->getScheme());

        $expected = 'https://example.com/path';
        $this->assertSame($expected, (string) $new);
        $expected = 'http://example.com/path';
        $this->assertSame($expected, (string) $uri);
    }

    public function testWithSchemeSetsEmpty()
    {
        $url = 'example.com';
        $uri = new URI('http://' . $url);

        $new = $uri->withScheme('');

        $this->assertSame($url, (string) $new);
        $this->assertSame('http://' . $url, (string) $uri);
    }

    public function testSetUserInfoSetsValue(): void
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $uri->setUserInfo('user', 'password');

        $this->assertSame('user', $uri->getUserInfo());
        $expected = 'http://user@example.com/path';
        $this->assertSame($expected, (string) $uri);
    }

    public function testUserInfoCanShowPassword(): void
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $uri->setUserInfo('user', 'password');

        $this->assertSame('user', $uri->getUserInfo());
        $expected = 'http://user@example.com/path';
        $this->assertSame($expected, (string) $uri);

        $uri->showPassword();

        $this->assertSame('user:password', $uri->getUserInfo());
        $expected = 'http://user:password@example.com/path';
        $this->assertSame($expected, (string) $uri);
    }

    public function testSetHostSetsValue(): void
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $uri->setHost('another.com');

        $this->assertSame('another.com', $uri->getHost());
        $expected = 'http://another.com/path';
        $this->assertSame($expected, (string) $uri);
    }

    public function testSetPortSetsValue(): void
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $uri->setPort(9000);

        $this->assertSame(9000, $uri->getPort());
        $expected = 'http://example.com:9000/path';
        $this->assertSame($expected, (string) $uri);
    }

    public function testSetPortInvalidValues(): void
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $errorString = lang('HTTP.invalidPort', [70000]);
        $this->assertNotEmpty($errorString);

        $this->expectException(HTTPException::class);
        $this->expectExceptionMessage(lang('HTTP.invalidPort', ['70000']));

        $uri->setPort(70000);
    }

    public function testSetPortInvalidValuesSilent(): void
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $uri->setSilent()->setPort(70000);

        $this->assertNull($uri->getPort());
    }

    public function testSetPortTooSmall(): void
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $this->expectException(HTTPException::class);
        $this->expectExceptionMessage(lang('HTTP.invalidPort', [-1]));

        $uri->setPort(-1);
    }

    public function testSetPortZero(): void
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $this->expectException(HTTPException::class);
        $this->expectExceptionMessage(lang('HTTP.invalidPort', [0]));

        $uri->setPort(0);
    }

    public function testCatchesBadPort(): void
    {
        $this->expectException(HTTPException::class);

        $url = 'http://username:password@hostname:90909/path?arg=value#anchor';
        $uri = new URI();
        $uri->setURI($url);
    }

    public function testSetPathSetsValue(): void
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $uri->setPath('somewhere/else');

        $this->assertSame('somewhere/else', $uri->getPath());
        $expected = 'http://example.com/somewhere/else';
        $this->assertSame($expected, (string) $uri);
    }

    /**
     * @dataProvider provideSetPath
     */
    public function testSetPath(string $path, string $expectedURL, string $expectedPath): void
    {
        $url = 'http://example.com/';
        $uri = new URI($url);

        $uri->setPath($path);

        $this->assertSame($expectedURL, (string) $uri);
        $this->assertSame($expectedPath, $uri->getPath());
    }

    public static function provideSetPath(): iterable
    {
        return [
            '' => [
                '',                   // path
                'http://example.com', // expectedURL
                '',                   // expectedPath
            ],
            '/' => [
                '/',
                'http://example.com/',
                '/',
            ],
            '/one/two' => [
                '/one/two',
                'http://example.com/one/two',
                '/one/two',
            ],
            '//one/two' => [
                '//one/two',
                'http://example.com/one/two',
                '/one/two',
            ],
            '/one/two/' => [
                '/one/two/',
                'http://example.com/one/two/',
                '/one/two/',
            ],
            '/one/two//' => [
                '/one/two//',
                'http://example.com/one/two/',
                '/one/two/',
            ],
            '//one/two//' => [
                '//one/two//',
                'http://example.com/one/two/',
                '/one/two/',
            ],
            '//one//two//' => [
                '//one//two//',
                'http://example.com/one/two/',
                '/one/two/',
            ],
            '///one/two' => [
                '///one/two',
                'http://example.com/one/two',
                '/one/two',
            ],
            '/one/two///' => [
                '/one/two///',                 // path
                'http://example.com/one/two/', // expectedURL
                '/one/two/',                   // expectedPath
            ],
        ];
    }

    public static function providePathGetsFiltered(): iterable
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
     * @dataProvider providePathGetsFiltered
     *
     * @param string $path
     * @param string $expected
     */
    public function testPathGetsFiltered($path, $expected): void
    {
        $uri = new URI();
        $uri->setPath($path);

        $this->assertSame($expected, $uri->getPath());
    }

    public function testSetFragmentSetsValue(): void
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $uri->setFragment('#good-stuff');

        $this->assertSame('good-stuff', $uri->getFragment());
        $expected = 'http://example.com/path#good-stuff';
        $this->assertSame($expected, (string) $uri);
    }

    public function testSetQuerySetsValue(): void
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $uri->setQuery('?key=value&second.key=value.2');

        $this->assertSame('key=value&second_key=value.2', $uri->getQuery());
        $expected = 'http://example.com/path?key=value&second_key=value.2';
        $this->assertSame($expected, (string) $uri);
    }

    public function testSetQuerySetsValueWithUseRawQueryString(): void
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $uri->useRawQueryString()->setQuery('?key=value&second.key=value.2');

        $this->assertSame('key=value&second.key=value.2', $uri->getQuery());
        $expected = 'http://example.com/path?key=value&second.key=value.2';
        $this->assertSame($expected, (string) $uri);
    }

    public function testSetQueryArraySetsValue(): void
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $uri->setQueryArray(['key' => 'value', 'second.key' => 'value.2']);

        $this->assertSame('key=value&second_key=value.2', $uri->getQuery());
        $expected = 'http://example.com/path?key=value&second_key=value.2';
        $this->assertSame($expected, (string) $uri);
    }

    public function testSetQueryArraySetsValueWithUseRawQueryString(): void
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $uri->useRawQueryString()->setQueryArray(['key' => 'value', 'second.key' => 'value.2']);

        $this->assertSame('key=value&second.key=value.2', $uri->getQuery());
        $expected = 'http://example.com/path?key=value&second.key=value.2';
        $this->assertSame($expected, (string) $uri);
    }

    public function testSetQueryThrowsErrorWhenFragmentPresent(): void
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $this->expectException(HTTPException::class);

        $uri->setQuery('?key=value#fragment');
    }

    public function testSetQueryThrowsErrorWhenFragmentPresentSilent(): void
    {
        $url = 'http://example.com/path';
        $uri = new URI($url);

        $uri->setSilent()->setQuery('?key=value#fragment');

        $this->assertSame('', $uri->getQuery());
    }

    public static function provideAuthorityReturnsExceptedValues(): iterable
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
     * @dataProvider provideAuthorityReturnsExceptedValues
     *
     * @param string $url
     * @param string $expected
     */
    public function testAuthorityReturnsExceptedValues($url, $expected): void
    {
        $uri = new URI($url);

        $this->assertSame($expected, $uri->getAuthority());
    }

    public static function provideAuthorityRemovesDefaultPorts(): iterable
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
     * @dataProvider provideAuthorityRemovesDefaultPorts
     *
     * @param string $scheme
     * @param int    $port
     */
    public function testAuthorityRemovesDefaultPorts($scheme, $port): void
    {
        $url = "{$scheme}://example.com:{$port}/path";
        $uri = new URI($url);

        $expected = "{$scheme}://example.com/path";
        $this->assertSame($expected, (string) $uri);
    }

    public function testSetAuthorityReconstitutes(): void
    {
        $authority = 'me@foo.com:3000';

        $uri = new URI();
        $uri->setAuthority($authority);

        $this->assertSame($authority, $uri->getAuthority());
    }

    public static function provideRemoveDotSegments(): iterable
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
     * @dataProvider provideRemoveDotSegments
     *
     * @param string $path
     * @param string $expected
     */
    public function testRemoveDotSegments($path, $expected): void
    {
        $this->assertSame($expected, URI::removeDotSegments($path));
    }

    public static function defaultResolutions(): iterable
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
     * @param string $rel
     * @param string $expected
     */
    public function testResolveRelativeURI($rel, $expected): void
    {
        $base = 'http://a/b/c/d';
        $uri  = new URI($base);

        $new = $uri->resolveRelativeURI($rel);

        $this->assertSame($expected, (string) $new);
    }

    /**
     * @dataProvider defaultResolutions
     *
     * @param string $rel
     * @param string $expected
     */
    public function testResolveRelativeURIHTTPS($rel, $expected): void
    {
        $base     = 'https://a/b/c/d';
        $expected = str_replace('http:', 'https:', $expected);

        $uri = new URI($base);

        $new = $uri->resolveRelativeURI($rel);

        $this->assertSame($expected, (string) $new);
    }

    public function testResolveRelativeURIWithNoBase(): void
    {
        $base = 'http://a';
        $uri  = new URI($base);

        $new = $uri->resolveRelativeURI('x');

        $this->assertSame('http://a/x', (string) $new);
    }

    public function testAddQueryVar(): void
    {
        $base = 'http://example.com/foo';
        $uri  = new URI($base);

        $uri->addQuery('bar', 'baz');

        $this->assertSame('http://example.com/foo?bar=baz', (string) $uri);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/pull/954
     */
    public function testSetQueryDecode(): void
    {
        $base    = 'http://example.com/foo';
        $uri     = new URI($base);
        $encoded = urlencode('you+alice+to+the+little');

        $uri->setQuery("q={$encoded}");

        // Should NOT be double-encoded, since http_build_query
        // will encode the value again.
        $this->assertSame("q={$encoded}", $uri->getQuery());
    }

    public function testAddQueryVarRespectsExistingQueryVars(): void
    {
        $base = 'http://example.com/foo?bar=baz';
        $uri  = new URI($base);

        $uri->addQuery('baz', 'foz');

        $this->assertSame('http://example.com/foo?bar=baz&baz=foz', (string) $uri);
    }

    public function testStripQueryVars(): void
    {
        $base = 'http://example.com/foo?foo=bar&bar=baz&baz=foz';
        $uri  = new URI($base);

        $uri->stripQuery('bar', 'baz');

        $this->assertSame('http://example.com/foo?foo=bar', (string) $uri);
    }

    public function testKeepQueryVars(): void
    {
        $base = 'http://example.com/foo?foo=bar&bar=baz&baz=foz';
        $uri  = new URI($base);

        $uri->keepQuery('bar', 'baz');

        $this->assertSame('http://example.com/foo?bar=baz&baz=foz', (string) $uri);
    }

    public function testEmptyQueryVars(): void
    {
        $base = 'http://example.com/foo';
        $uri  = new URI($base);

        $uri->setQuery('foo=&bar=baz&baz=foz');

        $this->assertSame('http://example.com/foo?foo=&bar=baz&baz=foz', (string) $uri);
    }

    public function testGetQueryExcept(): void
    {
        $base = 'http://example.com/foo?foo=bar&bar=baz&baz=foz';
        $uri  = new URI($base);

        $this->assertSame('foo=bar&baz=foz', $uri->getQuery(['except' => ['bar']]));
    }

    public function testGetQueryOnly(): void
    {
        $base = 'http://example.com/foo?foo=bar&bar=baz&baz=foz';
        $uri  = new URI($base);

        $this->assertSame('bar=baz', $uri->getQuery(['only' => ['bar']]));
        $this->assertSame('foo=bar&baz=foz', $uri->getQuery(['except' => 'bar']));
    }

    public function testGetQueryWithStrings(): void
    {
        $base = 'http://example.com/foo?foo=bar&bar=baz&baz=foz';
        $uri  = new URI($base);

        $this->assertSame('bar=baz', $uri->getQuery(['only' => 'bar']));
    }

    /**
     * @see   https://github.com/codeigniter4/CodeIgniter4/issues/331
     */
    public function testNoExtraSlashes(): void
    {
        $this->assertSame(
            'http://entirely.different.com/subfolder',
            (string) (new URI('entirely.different.com/subfolder'))
        );
        $this->assertSame(
            'http://localhost/subfolder',
            (string) (new URI('localhost/subfolder'))
        );
        $this->assertSame(
            'http://localtest.me/subfolder',
            (string) (new URI('localtest.me/subfolder'))
        );
    }

    public function testSetSegment(): void
    {
        $base = 'http://example.com/foo/bar/baz';
        $uri  = new URI($base);

        $uri->setSegment(2, 'banana');

        $this->assertSame('foo/banana/baz', $uri->getPath());
    }

    public function testSetSegmentNewOne(): void
    {
        $base = 'http://example.com';
        $uri  = new URI($base);

        // Can set the next segment.
        $uri->setSegment(1, 'first');
        // Can set the next segment.
        $uri->setSegment(2, 'third');

        $this->assertSame('first/third', $uri->getPath());

        // Can replace the existing segment.
        $uri->setSegment(2, 'second');

        $this->assertSame('first/second', $uri->getPath());

        // Can set the next segment.
        $uri->setSegment(3, 'third');

        $this->assertSame('first/second/third', $uri->getPath());

        // Can set the next segment.
        $uri->setSegment(4, 'fourth');

        $this->assertSame('first/second/third/fourth', $uri->getPath());

        // Cannot set the next next segment.
        $this->expectException(HTTPException::class);

        $uri->setSegment(6, 'six');
    }

    public function testSetBadSegment(): void
    {
        $this->expectException(HTTPException::class);

        $base = 'http://example.com/foo/bar/baz';
        $uri  = new URI($base);

        $uri->setSegment(6, 'banana');
    }

    public function testSetBadSegmentSilent(): void
    {
        $base     = 'http://example.com/foo/bar/baz';
        $uri      = new URI($base);
        $segments = $uri->getSegments();

        $uri->setSilent()->setSegment(6, 'banana');

        $this->assertSame($segments, $uri->getSegments());
    }

    // Exploratory testing, investigating https://github.com/codeigniter4/CodeIgniter4/issues/2016

    public function testBasedNoIndex(): void
    {
        $_SERVER['REQUEST_URI']  = '/ci/v4/controller/method';
        $_SERVER['SCRIPT_NAME']  = '/ci/v4/index.php';
        $_SERVER['QUERY_STRING'] = '';
        $_SERVER['HTTP_HOST']    = 'example.com';
        $_SERVER['PATH_INFO']    = '/controller/method';

        $this->resetServices();

        $config            = new App();
        $config->baseURL   = 'http://example.com/ci/v4/';
        $config->indexPage = '';
        Factories::injectMock('config', 'App', $config);

        $request = Services::request($config);
        Services::injectMock('request', $request);

        // going through request
        $this->assertSame(
            'http://example.com/ci/v4/controller/method',
            (string) $request->getUri()
        );
        $this->assertSame('/ci/v4/controller/method', $request->getUri()->getPath());
        $this->assertSame('controller/method', $request->getUri()->getRoutePath());

        // standalone
        $uri = new URI('http://example.com/ci/v4/controller/method');
        $this->assertSame('http://example.com/ci/v4/controller/method', (string) $uri);
        $this->assertSame('/ci/v4/controller/method', $uri->getPath());

        $this->assertSame($uri->getPath(), $request->getUri()->getPath());
    }

    public function testBasedWithIndex(): void
    {
        $_SERVER['REQUEST_URI']  = '/ci/v4/index.php/controller/method';
        $_SERVER['SCRIPT_NAME']  = '/ci/v4/index.php';
        $_SERVER['QUERY_STRING'] = '';
        $_SERVER['HTTP_HOST']    = 'example.com';
        $_SERVER['PATH_INFO']    = '/controller/method';

        $this->resetServices();

        $config            = new App();
        $config->baseURL   = 'http://example.com/ci/v4/';
        $config->indexPage = 'index.php';
        Factories::injectMock('config', 'App', $config);

        $request = Services::request($config);
        Services::injectMock('request', $request);

        // going through request
        $this->assertSame(
            'http://example.com/ci/v4/index.php/controller/method',
            (string) $request->getUri()
        );
        $this->assertSame(
            '/ci/v4/index.php/controller/method',
            $request->getUri()->getPath()
        );

        // standalone
        $uri = new URI('http://example.com/ci/v4/index.php/controller/method');
        $this->assertSame(
            'http://example.com/ci/v4/index.php/controller/method',
            (string) $uri
        );
        $this->assertSame('/ci/v4/index.php/controller/method', $uri->getPath());

        $this->assertSame($uri->getPath(), $request->getUri()->getPath());
    }

    public function testForceGlobalSecureRequests(): void
    {
        $this->resetServices();

        $_SERVER['REQUEST_URI']  = '/ci/v4/controller/method';
        $_SERVER['SCRIPT_NAME']  = '/ci/v4/index.php';
        $_SERVER['QUERY_STRING'] = '';
        $_SERVER['HTTP_HOST']    = 'example.com';
        $_SERVER['PATH_INFO']    = '/controller/method';

        $config                            = new App();
        $config->baseURL                   = 'http://example.com/ci/v4';
        $config->indexPage                 = '';
        $config->forceGlobalSecureRequests = true;
        Factories::injectMock('config', 'App', $config);

        $request = Services::request($config);
        Services::injectMock('request', $request);

        // Detected by request
        $this->assertSame(
            'https://example.com/ci/v4/controller/method',
            (string) $request->getUri()
        );

        // Standalone
        $uri = new URI('http://example.com/ci/v4/controller/method');
        $this->assertSame('https://example.com/ci/v4/controller/method', (string) $uri);

        $this->assertSame(
            trim($uri->getPath(), '/'),
            trim($request->getUri()->getPath(), '/')
        );
    }

    public function testZeroAsURIPath(): void
    {
        $url = 'http://example.com/0';
        $uri = new URI($url);

        $this->assertSame($url, (string) $uri);
        $this->assertSame('/0', $uri->getPath());
    }

    public function testEmptyURIPath(): void
    {
        $url = 'http://example.com/';
        $uri = new URI($url);

        $this->assertSame('/', $uri->getPath());
        $this->assertSame([], $uri->getSegments());
        $this->assertSame(0, $uri->getTotalSegments());
    }

    public function testSetURI(): void
    {
        $url = ':';
        $uri = new URI();

        $this->expectException(HTTPException::class);
        $this->expectExceptionMessage(lang('HTTP.cannotParseURI', [$url]));

        $uri->setURI($url);
    }

    public function testSetURISilent(): void
    {
        $url = ':';
        $uri = new URI();

        $uri->setSilent()->setURI($url);

        $this->assertTrue(true);
    }

    public function testCreateURIStringNoArguments(): void
    {
        $uri = URI::createURIString();

        $expected = '';
        $this->assertSame($expected, $uri);
    }

    public function testCreateURIStringOnlyAuthority(): void
    {
        $uri = URI::createURIString(null, 'example.com');

        $expected = 'example.com';
        $this->assertSame($expected, $uri);
    }

    public function testCreateURIString(): void
    {
        $uri = URI::createURIString('https', 'example.com', '/');

        $expected = 'https://example.com/';
        $this->assertSame($expected, $uri);
    }

    public function testCreateURIStringAuthorityMisuseEndWithSlash(): void
    {
        $uri = URI::createURIString('https', 'example.com/', '/');

        $expected = 'https://example.com/';
        $this->assertSame($expected, $uri);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5728
     */
    public function testForceGlobalSecureRequestsAndNonHTTPProtocol(): void
    {
        $config                            = new App();
        $config->forceGlobalSecureRequests = true;
        $config->baseURL                   = 'https://localhost/';
        Factories::injectMock('config', 'App', $config);

        $expected = 'ftp://localhost/path/to/test.txt';
        $uri      = new URI($expected);

        $this->assertSame($expected, (string) $uri);
    }
}
