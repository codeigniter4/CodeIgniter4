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

use CodeIgniter\Config\Services;
use CodeIgniter\Superglobals;
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
final class SiteURIFactoryTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Services::injectMock('superglobals', new Superglobals([], []));
    }

    private function createSiteURIFactory(?App $config = null, ?Superglobals $superglobals = null): SiteURIFactory
    {
        $config ??= new App();
        $superglobals ??= new Superglobals();

        return new SiteURIFactory($config, $superglobals);
    }

    public function testCreateFromGlobals(): void
    {
        // http://localhost:8080/index.php/woot?code=good#pos
        service('superglobals')
            ->setServer('REQUEST_URI', '/index.php/woot?code=good')
            ->setServer('SCRIPT_NAME', '/index.php')
            ->setServer('QUERY_STRING', 'code=good')
            ->setServer('HTTP_HOST', 'localhost:8080')
            ->setServer('PATH_INFO', '/woot')
            ->setGet('code', 'good');

        $factory = $this->createSiteURIFactory();

        $uri = $factory->createFromGlobals();

        $this->assertInstanceOf(SiteURI::class, $uri);
        $this->assertSame('http://localhost:8080/index.php/woot?code=good', (string) $uri);
        $this->assertSame('/index.php/woot', $uri->getPath());
        $this->assertSame('woot', $uri->getRoutePath());
    }

    public function testCreateFromGlobalsAllowedHost(): void
    {
        // http://users.example.jp/index.php/woot?code=good#pos
        service('superglobals')
            ->setServer('REQUEST_URI', '/index.php/woot?code=good')
            ->setServer('SCRIPT_NAME', '/index.php')
            ->setServer('QUERY_STRING', 'code=good')
            ->setServer('HTTP_HOST', 'users.example.jp')
            ->setServer('PATH_INFO', '/woot')
            ->setGet('code', 'good');

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

    #[DataProvider('provideCreateFromStringWithIndexPage')]
    public function testCreateFromStringWithIndexPage(
        string $uriString,
        string $expectUriString,
        string $expectedPath,
        string $expectedRoutePath,
    ): void {
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

    #[DataProvider('provideCreateFromStringWithoutIndexPage')]
    public function testCreateFromStringWithoutIndexPage(
        string $uriString,
        string $expectUriString,
        string $expectedPath,
        string $expectedRoutePath,
    ): void {
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
