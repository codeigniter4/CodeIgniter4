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

namespace CodeIgniter\HTTP;

use BadMethodCallException;
use CodeIgniter\Exceptions\ConfigException;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[BackupGlobals(true)]
#[Group('Others')]
final class SiteURITest extends CIUnitTestCase
{
    #[DataProvider('provideConstructor')]
    public function testConstructor(
        string $baseURL,
        string $indexPage,
        string $relativePath,
        string $expectedURI,
        string $expectedRoutePath,
        string $expectedPath,
        string $expectedQuery,
        string $expectedFragment,
        array $expectedSegments,
        int $expectedTotalSegments
    ): void {
        $config            = new App();
        $config->indexPage = $indexPage;
        $config->baseURL   = $baseURL;

        $uri = new SiteURI($config, $relativePath);

        $this->assertInstanceOf(SiteURI::class, $uri);

        $this->assertSame($expectedURI, (string) $uri);
        $this->assertSame($expectedRoutePath, $uri->getRoutePath());
        $this->assertSame($expectedPath, $uri->getPath());
        $this->assertSame($expectedQuery, $uri->getQuery());
        $this->assertSame($expectedFragment, $uri->getFragment());
        $this->assertSame($baseURL, $uri->getBaseURL());

        $this->assertSame($expectedSegments, $uri->getSegments());
        $this->assertSame($expectedTotalSegments, $uri->getTotalSegments());
    }

    public static function provideConstructor(): iterable
    {
        return array_merge(self::provideSetPath(), self::provideRelativePathWithQueryOrFragment());
    }

    public static function provideSetPath(): iterable
    {
        return [
            '' => [
                'http://example.com/',          // $baseURL
                'index.php',                    // $indexPage
                '',                             // $relativePath
                'http://example.com/index.php', // $expectedURI
                '',                             // $expectedRoutePath
                '/index.php',                   // $expectedPath
                '',                             // $expectedQuery
                '',                             // $expectedFragment
                [],                             // $expectedSegments
                0,                              // $expectedTotalSegments
            ],
            '/' => [
                'http://example.com/',
                'index.php',
                '/',
                'http://example.com/index.php/',
                '',
                '/index.php/',
                '',
                '',
                [],
                0,
            ],
            'one/two' => [
                'http://example.com/',
                'index.php',
                'one/two',
                'http://example.com/index.php/one/two',
                'one/two',
                '/index.php/one/two', '',
                '',
                ['one', 'two'],
                2,
            ],
            '/one/two' => [
                'http://example.com/',
                'index.php',
                '/one/two',
                'http://example.com/index.php/one/two',
                'one/two',
                '/index.php/one/two',
                '',
                '',
                ['one', 'two'],
                2,
            ],
            '/one/two/' => [
                'http://example.com/',
                'index.php',
                '/one/two/',
                'http://example.com/index.php/one/two/',
                'one/two/',
                '/index.php/one/two/',
                '',
                '',
                ['one', 'two'],
                2,
            ],
            '//one/two' => [
                'http://example.com/',
                'index.php',
                '//one/two',
                'http://example.com/index.php/one/two',
                'one/two',
                '/index.php/one/two',
                '',
                '',
                ['one', 'two'],
                2,
            ],
            'one/two//' => [
                'http://example.com/',
                'index.php',
                'one/two//',
                'http://example.com/index.php/one/two/',
                'one/two/',
                '/index.php/one/two/',
                '',
                '',
                ['one', 'two'],
                2,
            ],
            '///one///two///' => [
                'http://example.com/',
                'index.php',
                '///one///two///',
                'http://example.com/index.php/one/two/',
                'one/two/',
                '/index.php/one/two/',
                '',
                '',
                ['one', 'two'],
                2,
            ],
            'Subfolder: ' => [
                'http://example.com/ci4/',
                'index.php',
                '',
                'http://example.com/ci4/index.php',
                '',
                '/ci4/index.php',
                '',
                '',
                [],
                0,
            ],
            'Subfolder: one/two' => [
                'http://example.com/ci4/',
                'index.php',
                'one/two',
                'http://example.com/ci4/index.php/one/two',
                'one/two',
                '/ci4/index.php/one/two',
                '',
                '',
                ['one', 'two'],
                2,
            ],
            'EmptyIndexPage: ' => [
                'http://example.com/',
                '',
                '',
                'http://example.com/',
                '',
                '/',
                '',
                '',
                [],
                0,
            ],
            'EmptyIndexPage: /' => [
                'http://example.com/',
                '',
                '/',
                'http://example.com/',
                '',
                '/',
                '',
                '',
                [],
                0,
            ],
        ];
    }

    public static function provideRelativePathWithQueryOrFragment(): iterable
    {
        return [
            'one/two?foo=1&bar=2' => [
                'http://example.com/',                              // $baseURL
                'index.php',                                        // $indexPage
                'one/two?foo=1&bar=2',                              // $relativePath
                'http://example.com/index.php/one/two?foo=1&bar=2', // $expectedURI
                'one/two',                                          // $expectedRoutePath
                '/index.php/one/two',                               // $expectedPath
                'foo=1&bar=2',                                      // $expectedQuery
                '',                                                 // $expectedFragment
                ['one', 'two'],                                     // $expectedSegments
                2,                                                  // $expectedTotalSegments
            ],
            'one/two#sec1' => [
                'http://example.com/',
                'index.php',
                'one/two#sec1',
                'http://example.com/index.php/one/two#sec1',
                'one/two',
                '/index.php/one/two',
                '',
                'sec1',
                ['one', 'two'],
                2,
            ],
            'one/two?foo=1&bar=2#sec1' => [
                'http://example.com/',
                'index.php',
                'one/two?foo=1&bar=2#sec1',
                'http://example.com/index.php/one/two?foo=1&bar=2#sec1',
                'one/two',
                '/index.php/one/two',
                'foo=1&bar=2',
                'sec1',
                ['one', 'two'],
                2,
            ],
            'Subfolder: one/two?foo=1&bar=2' => [
                'http://example.com/ci4/',
                'index.php',
                'one/two?foo=1&bar=2',
                'http://example.com/ci4/index.php/one/two?foo=1&bar=2',
                'one/two',
                '/ci4/index.php/one/two',
                'foo=1&bar=2',
                '',
                ['one', 'two'],
                2,
            ],
        ];
    }

    public function testConstructorHost(): void
    {
        $config                   = new App();
        $config->allowedHostnames = ['sub.example.com'];

        $uri = new SiteURI($config, '', 'sub.example.com');

        $this->assertInstanceOf(SiteURI::class, $uri);
        $this->assertSame('http://sub.example.com/index.php', (string) $uri);
        $this->assertSame('', $uri->getRoutePath());
        $this->assertSame('/index.php', $uri->getPath());
        $this->assertSame('http://sub.example.com/', $uri->getBaseURL());
    }

    public function testConstructorScheme(): void
    {
        $config = new App();

        $uri = new SiteURI($config, '', null, 'https');

        $this->assertInstanceOf(SiteURI::class, $uri);
        $this->assertSame('https://example.com/index.php', (string) $uri);
        $this->assertSame('https://example.com/', $uri->getBaseURL());
    }

    public function testConstructorEmptyScheme(): void
    {
        $config = new App();

        $uri = new SiteURI($config, '', null, '');

        $this->assertInstanceOf(SiteURI::class, $uri);
        $this->assertSame('http://example.com/index.php', (string) $uri);
        $this->assertSame('http://example.com/', $uri->getBaseURL());
    }

    public function testConstructorForceGlobalSecureRequests(): void
    {
        $config                            = new App();
        $config->forceGlobalSecureRequests = true;

        $uri = new SiteURI($config);

        $this->assertSame('https://example.com/index.php', (string) $uri);
        $this->assertSame('https://example.com/', $uri->getBaseURL());
    }

    public function testConstructorInvalidBaseURL(): void
    {
        $this->expectException(ConfigException::class);

        $config          = new App();
        $config->baseURL = 'invalid';

        new SiteURI($config);
    }

    #[DataProvider('provideSetPath')]
    public function testSetPath(
        string $baseURL,
        string $indexPage,
        string $relativePath,
        string $expectedURI,
        string $expectedRoutePath,
        string $expectedPath,
        string $expectedQuery,
        string $expectedFragment,
        array $expectedSegments,
        int $expectedTotalSegments
    ): void {
        $config            = new App();
        $config->indexPage = $indexPage;
        $config->baseURL   = $baseURL;

        $uri = new SiteURI($config);

        $uri->setPath($relativePath);

        $this->assertSame($expectedURI, (string) $uri);
        $this->assertSame($expectedRoutePath, $uri->getRoutePath());
        $this->assertSame($expectedPath, $uri->getPath());
        $this->assertSame($expectedQuery, $uri->getQuery());
        $this->assertSame($expectedFragment, $uri->getFragment());
        $this->assertSame($baseURL, $uri->getBaseURL());

        $this->assertSame($expectedSegments, $uri->getSegments());
        $this->assertSame($expectedTotalSegments, $uri->getTotalSegments());
    }

    public function testSetSegment(): void
    {
        $config = new App();

        $uri = new SiteURI($config);
        $uri->setPath('test/method');

        $uri->setSegment(1, 'one');

        $this->assertSame('http://example.com/index.php/one/method', (string) $uri);
        $this->assertSame('one/method', $uri->getRoutePath());
        $this->assertSame('/index.php/one/method', $uri->getPath());
        $this->assertSame(['one', 'method'], $uri->getSegments());
        $this->assertSame('one', $uri->getSegment(1));
        $this->assertSame(2, $uri->getTotalSegments());
    }

    public function testSetSegmentOutOfRange(): void
    {
        $this->expectException(HTTPException::class);

        $config = new App();
        $uri    = new SiteURI($config);
        $uri->setPath('test/method');

        $uri->setSegment(4, 'four');
    }

    public function testSetSegmentSilentOutOfRange(): void
    {
        $config = new App();
        $uri    = new SiteURI($config);
        $uri->setPath('one/method');
        $uri->setSilent();

        $uri->setSegment(4, 'four');
        $this->assertSame(['one', 'method'], $uri->getSegments());
    }

    public function testSetSegmentZero(): void
    {
        $this->expectException(HTTPException::class);

        $config = new App();
        $uri    = new SiteURI($config);
        $uri->setPath('test/method');

        $uri->setSegment(0, 'four');
    }

    public function testSetSegmentSubfolder(): void
    {
        $config          = new App();
        $config->baseURL = 'http://example.com/ci4/';

        $uri = new SiteURI($config);
        $uri->setPath('test/method');

        $uri->setSegment(1, 'one');

        $this->assertSame('http://example.com/ci4/index.php/one/method', (string) $uri);
        $this->assertSame('one/method', $uri->getRoutePath());
        $this->assertSame('/ci4/index.php/one/method', $uri->getPath());
        $this->assertSame(['one', 'method'], $uri->getSegments());
        $this->assertSame('one', $uri->getSegment(1));
        $this->assertSame(2, $uri->getTotalSegments());
    }

    public function testGetRoutePath(): void
    {
        $config = new App();
        $uri    = new SiteURI($config);

        $this->assertSame('', $uri->getRoutePath());
    }

    public function testGetSegments(): void
    {
        $config = new App();
        $uri    = new SiteURI($config);

        $this->assertSame([], $uri->getSegments());
    }

    public function testGetSegmentZero(): void
    {
        $this->expectException(HTTPException::class);

        $config = new App();
        $uri    = new SiteURI($config);
        $uri->setPath('test/method');

        $uri->getSegment(0);
    }

    public function testGetSegmentOutOfRange(): void
    {
        $this->expectException(HTTPException::class);

        $config = new App();
        $uri    = new SiteURI($config);
        $uri->setPath('test/method');

        $uri->getSegment(4);
    }

    public function testGetTotalSegments(): void
    {
        $config = new App();
        $uri    = new SiteURI($config);

        $this->assertSame(0, $uri->getTotalSegments());
    }

    public function testSetURI(): void
    {
        $this->expectException(BadMethodCallException::class);

        $config = new App();
        $uri    = new SiteURI($config);

        $uri->setURI('http://another.site.example.jp/');
    }

    public function testSetBaseURI(): void
    {
        $this->expectException(BadMethodCallException::class);

        $config = new App();
        $uri    = new SiteURI($config);

        $uri->setBaseURL('http://another.site.example.jp/');
    }

    public function testGetBaseURL(): void
    {
        $config = new App();
        $uri    = new SiteURI($config);

        $this->assertSame('http://example.com/', $uri->getBaseURL());
    }
}
