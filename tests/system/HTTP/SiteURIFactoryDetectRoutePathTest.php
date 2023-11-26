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
final class SiteURIFactoryDetectRoutePathTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_GET = $_SERVER = [];
    }

    private function createSiteURIFactory(array $server, ?App $appConfig = null): SiteURIFactory
    {
        $appConfig ??= new App();

        $_SERVER      = $server;
        $superglobals = new Superglobals();

        return new SiteURIFactory($appConfig, $superglobals);
    }

    public function testDefault()
    {
        // /index.php/woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/index.php/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath());
    }

    public function testDefaultEmpty()
    {
        // /
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = '/';
        $this->assertSame($expected, $factory->detectRoutePath());
    }

    public function testRequestURI()
    {
        // /index.php/woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/index.php/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURINested()
    {
        // I'm not sure but this is a case of Apache config making such SERVER
        // values?
        // The current implementation doesn't use the value of the URI object.
        // So I removed the code to set URI. Therefore, it's exactly the same as
        // the method above as a test.
        // But it may be changed in the future to use the value of the URI object.
        // So I don't remove this test case.

        // /ci/index.php/woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/index.php/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURISubfolder()
    {
        // /ci/index.php/popcorn/woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/ci/index.php/popcorn/woot';
        $_SERVER['SCRIPT_NAME'] = '/ci/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = 'popcorn/woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURINoIndex()
    {
        // /sub/example
        $_SERVER['REQUEST_URI'] = '/sub/example';
        $_SERVER['SCRIPT_NAME'] = '/sub/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = 'example';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURINginx()
    {
        // /ci/index.php/woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/index.php/woot?code=good';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURINginxRedirecting()
    {
        // /?/ci/index.php/woot
        $_SERVER['REQUEST_URI'] = '/?/ci/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = 'ci/woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURISuppressed()
    {
        // /woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/woot';
        $_SERVER['SCRIPT_NAME'] = '/';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURIGetPath()
    {
        // /index.php/fruits/banana
        $_SERVER['REQUEST_URI'] = '/index.php/fruits/banana';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $this->assertSame('fruits/banana', $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURIPathIsRelative()
    {
        // /sub/folder/index.php/fruits/banana
        $_SERVER['REQUEST_URI'] = '/sub/folder/index.php/fruits/banana';
        $_SERVER['SCRIPT_NAME'] = '/sub/folder/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $this->assertSame('fruits/banana', $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURIStoresDetectedPath()
    {
        // /fruits/banana
        $_SERVER['REQUEST_URI'] = '/fruits/banana';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $_SERVER['REQUEST_URI'] = '/candy/snickers';

        $this->assertSame('fruits/banana', $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURIPathIsNeverRediscovered()
    {
        $_SERVER['REQUEST_URI'] = '/fruits/banana';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $_SERVER['REQUEST_URI'] = '/candy/snickers';
        $factory->detectRoutePath('REQUEST_URI');

        $this->assertSame('fruits/banana', $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testQueryString()
    {
        // /index.php?/ci/woot
        $_SERVER['REQUEST_URI']  = '/index.php?/ci/woot';
        $_SERVER['QUERY_STRING'] = '/ci/woot';
        $_SERVER['SCRIPT_NAME']  = '/index.php';

        $_GET['/ci/woot'] = '';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = 'ci/woot';
        $this->assertSame($expected, $factory->detectRoutePath('QUERY_STRING'));
    }

    public function testQueryStringWithQueryString()
    {
        // /index.php?/ci/woot?code=good#pos
        $_SERVER['REQUEST_URI']  = '/index.php?/ci/woot?code=good';
        $_SERVER['QUERY_STRING'] = '/ci/woot?code=good';
        $_SERVER['SCRIPT_NAME']  = '/index.php';

        $_GET['/ci/woot?code'] = 'good';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = 'ci/woot';
        $this->assertSame($expected, $factory->detectRoutePath('QUERY_STRING'));
        $this->assertSame('code=good', $_SERVER['QUERY_STRING']);
        $this->assertSame(['code' => 'good'], $_GET);
    }

    public function testQueryStringEmpty()
    {
        // /index.php?
        $_SERVER['REQUEST_URI'] = '/index.php?';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = '/';
        $this->assertSame($expected, $factory->detectRoutePath('QUERY_STRING'));
    }

    public function testPathInfoUnset()
    {
        // /index.php/woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/index.php/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createSiteURIFactory($_SERVER);

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('PATH_INFO'));
    }

    public function testPathInfoSubfolder()
    {
        $appConfig          = new App();
        $appConfig->baseURL = 'http://localhost:8888/ci431/public/';

        // http://localhost:8888/ci431/public/index.php/woot?code=good#pos
        $_SERVER['PATH_INFO']   = '/woot';
        $_SERVER['REQUEST_URI'] = '/ci431/public/index.php/woot?code=good';
        $_SERVER['SCRIPT_NAME'] = '/ci431/public/index.php';

        $factory = $this->createSiteURIFactory($_SERVER, $appConfig);

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('PATH_INFO'));
    }

    /**
     * @dataProvider provideExtensionPHP
     *
     * @param string $path
     * @param string $detectPath
     */
    public function testExtensionPHP($path, $detectPath)
    {
        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $_SERVER['REQUEST_URI'] = $path;
        $_SERVER['SCRIPT_NAME'] = $path;

        $factory = $this->createSiteURIFactory($_SERVER, $config);

        $this->assertSame($detectPath, $factory->detectRoutePath());
    }

    public static function provideExtensionPHP(): iterable
    {
        return [
            'not /index.php' => [
                '/test.php',
                '/',
            ],
            '/index.php' => [
                '/index.php',
                '/',
            ],
        ];
    }
}
