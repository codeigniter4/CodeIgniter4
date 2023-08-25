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

use CodeIgniter\Superglobals;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;

/**
 * @backupGlobals enabled
 *
 * @internal
 *
 * @group Others
 */
final class SiteURIFactoryTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_GET = $_SERVER = [];
    }

    private function createSiteURIFactory(?App $config = null, ?Superglobals $superglobals = null): SiteURIFactory
    {
        $config ??= new App();
        $superglobals ??= new Superglobals();

        return new SiteURIFactory($config, $superglobals);
    }

    public function testCreateFromGlobals()
    {
        // http://localhost:8080/index.php/woot?code=good#pos
        $_SERVER['REQUEST_URI']  = '/index.php/woot?code=good';
        $_SERVER['SCRIPT_NAME']  = '/index.php';
        $_SERVER['QUERY_STRING'] = 'code=good';
        $_SERVER['HTTP_HOST']    = 'localhost:8080';
        $_SERVER['PATH_INFO']    = '/woot';

        $_GET['code'] = 'good';

        $factory = $this->createSiteURIFactory();

        $uri = $factory->createFromGlobals();

        $this->assertInstanceOf(SiteURI::class, $uri);
        $this->assertSame('http://localhost:8080/index.php/woot?code=good', (string) $uri);
        $this->assertSame('/index.php/woot', $uri->getPath());
        $this->assertSame('woot', $uri->getRoutePath());
    }

    public function testCreateFromGlobalsAllowedHost()
    {
        // http://users.example.jp/index.php/woot?code=good#pos
        $_SERVER['REQUEST_URI']  = '/index.php/woot?code=good';
        $_SERVER['SCRIPT_NAME']  = '/index.php';
        $_SERVER['QUERY_STRING'] = 'code=good';
        $_SERVER['HTTP_HOST']    = 'users.example.jp';
        $_SERVER['PATH_INFO']    = '/woot';

        $_GET['code'] = 'good';

        $config                   = new App();
        $config->baseURL          = 'http://example.jp/';
        $config->allowedHostnames = ['users.example.jp'];

        $factory = $this->createSiteURIFactory($config);

        $uri = $factory->createFromGlobals();

        $this->assertInstanceOf(SiteURI::class, $uri);
        $this->assertSame('http://users.example.jp/index.php/woot?code=good', (string) $uri);
        $this->assertSame('/index.php/woot', $uri->getPath());
        $this->assertSame('woot', $uri->getRoutePath());
    }

    /**
     * @dataProvider provideCreateFromStringWithIndexPage
     */
    public function testCreateFromStringWithIndexPage(
        string $uriString,
        string $expectUriString,
        string $expectedPath,
        string $expectedRoutePath
    ) {
        $factory = $this->createSiteURIFactory();

        $uri = $factory->createFromString($uriString);

        $this->assertInstanceOf(SiteURI::class, $uri);
        $this->assertSame($expectUriString, (string) $uri);
        $this->assertSame($expectedPath, $uri->getPath());
        $this->assertSame($expectedRoutePath, $uri->getRoutePath());
    }

    public static function provideCreateFromStringWithIndexPage(): iterable
    {
        return [
            'indexPage path query' => [
                'http://invalid.example.jp/foo/bar?page=3',         // $uriString
                'http://localhost:8080/index.php/foo/bar?page=3',   // $expectUriString
                '/index.php/foo/bar',                               // $expectedPath
                'foo/bar',                                          // $expectedRoutePath
            ],
            'indexPage noPath' => [
                'http://localhost:8080',            // $uriString
                'http://localhost:8080/index.php',  // $expectUriString
                '/index.php',                       // $expectedPath
                '',                                 // $expectedRoutePath
            ],
            'indexPage slash' => [
                'http://localhost:8080/',            // $uriString
                'http://localhost:8080/index.php/',  // $expectUriString
                '/index.php/',                       // $expectedPath
                '',                                  // $expectedRoutePath
            ],
        ];
    }

    /**
     * @dataProvider provideCreateFromStringWithoutIndexPage
     */
    public function testCreateFromStringWithoutIndexPage(
        string $uriString,
        string $expectUriString,
        string $expectedPath,
        string $expectedRoutePath
    ) {
        $config            = new App();
        $config->indexPage = '';
        $factory           = $this->createSiteURIFactory($config);

        $uri = $factory->createFromString($uriString);

        $this->assertInstanceOf(SiteURI::class, $uri);
        $this->assertSame($expectUriString, (string) $uri);
        $this->assertSame($expectedPath, $uri->getPath());
        $this->assertSame($expectedRoutePath, $uri->getRoutePath());
    }

    public static function provideCreateFromStringWithoutIndexPage(): iterable
    {
        return [
            'path query' => [
                'http://invalid.example.jp/foo/bar?page=3', // $uriString
                'http://localhost:8080/foo/bar?page=3',     // $expectUriString
                '/foo/bar',                                 // $expectedPath
                'foo/bar',                                  // $expectedRoutePath
            ],
            'noPath' => [
                'http://localhost:8080',   // $uriString
                'http://localhost:8080/',  // $expectUriString
                '/',                       // $expectedPath
                '',                        // $expectedRoutePath
            ],
            'slash' => [
                'http://localhost:8080/',  // $uriString
                'http://localhost:8080/',  // $expectUriString
                '/',                       // $expectedPath
                '',                        // $expectedRoutePath
            ],
        ];
    }
}
