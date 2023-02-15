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

    public function testCreateFromGlobals()
    {
        // http://localhost:8080/index.php/woot?code=good#pos
        $_SERVER['REQUEST_URI']  = '/index.php/woot?code=good';
        $_SERVER['SCRIPT_NAME']  = '/index.php';
        $_SERVER['QUERY_STRING'] = 'code=good';
        $_SERVER['HTTP_HOST']    = 'localhost:8080';
        $_SERVER['PATH_INFO']    = '/woot';

        $_GET['code'] = 'good';

        $factory = new SiteURIFactory($_SERVER, new App());

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

        $factory = new SiteURIFactory($_SERVER, $config);

        $uri = $factory->createFromGlobals();

        $this->assertInstanceOf(SiteURI::class, $uri);
        $this->assertSame('http://users.example.jp/index.php/woot?code=good', (string) $uri);
        $this->assertSame('/index.php/woot', $uri->getPath());
        $this->assertSame('woot', $uri->getRoutePath());
    }

    public function testCreateFromString()
    {
        $factory = new SiteURIFactory($_SERVER, new App());

        $uriString = 'http://invalid.example.jp/foo/bar?page=3';
        $uri       = $factory->createFromString($uriString);

        $this->assertInstanceOf(SiteURI::class, $uri);
        $this->assertSame('http://localhost:8080/index.php/foo/bar?page=3', (string) $uri);
        $this->assertSame('/index.php/foo/bar', $uri->getPath());
        $this->assertSame('foo/bar', $uri->getRoutePath());
    }
}
