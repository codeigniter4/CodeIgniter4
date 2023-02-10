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
final class URIFactoryDetectRoutePathTest extends CIUnitTestCase
{
    private function createURIFactory(array &$server, array &$get, ?App $appConfig = null): URIFactory
    {
        $appConfig ??= new App();

        return new URIFactory($server, $get, $appConfig);
    }

    public function testDefault()
    {
        $_GET = $_SERVER = [];

        // /index.php/woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/index.php/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createURIFactory($_SERVER, $_GET);

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath());
    }

    public function testDefaultEmpty()
    {
        $_GET = $_SERVER = [];

        // /
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createURIFactory($_SERVER, $_GET);

        $expected = '/';
        $this->assertSame($expected, $factory->detectRoutePath());
    }

    public function testRequestURI()
    {
        $_GET = $_SERVER = [];

        // /index.php/woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/index.php/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createURIFactory($_SERVER, $_GET);

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURINested()
    {
        $_GET = $_SERVER = [];

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

        $factory = $this->createURIFactory($_SERVER, $_GET);

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURISubfolder()
    {
        $_GET = $_SERVER = [];

        // /ci/index.php/popcorn/woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/ci/index.php/popcorn/woot';
        $_SERVER['SCRIPT_NAME'] = '/ci/index.php';

        $factory = $this->createURIFactory($_SERVER, $_GET);

        $expected = 'popcorn/woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURINoIndex()
    {
        $_GET = $_SERVER = [];

        // /sub/example
        $_SERVER['REQUEST_URI'] = '/sub/example';
        $_SERVER['SCRIPT_NAME'] = '/sub/index.php';

        $factory = $this->createURIFactory($_SERVER, $_GET);

        $expected = 'example';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURINginx()
    {
        $_GET = $_SERVER = [];

        // /ci/index.php/woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/index.php/woot?code=good';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createURIFactory($_SERVER, $_GET);

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURINginxRedirecting()
    {
        $_GET = $_SERVER = [];

        // /?/ci/index.php/woot
        $_SERVER['REQUEST_URI'] = '/?/ci/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createURIFactory($_SERVER, $_GET);

        $expected = 'ci/woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURISuppressed()
    {
        $_GET = $_SERVER = [];

        // /woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/woot';
        $_SERVER['SCRIPT_NAME'] = '/';

        $factory = $this->createURIFactory($_SERVER, $_GET);

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testQueryString()
    {
        $_GET = $_SERVER = [];

        // /index.php?/ci/woot
        $_SERVER['REQUEST_URI']  = '/index.php?/ci/woot';
        $_SERVER['QUERY_STRING'] = '/ci/woot';
        $_SERVER['SCRIPT_NAME']  = '/index.php';

        $_GET['/ci/woot'] = '';

        $factory = $this->createURIFactory($_SERVER, $_GET);

        $expected = 'ci/woot';
        $this->assertSame($expected, $factory->detectRoutePath('QUERY_STRING'));
    }

    public function testQueryStringWithQueryString()
    {
        $_GET = $_SERVER = [];

        // /index.php?/ci/woot?code=good#pos
        $_SERVER['REQUEST_URI']  = '/index.php?/ci/woot?code=good';
        $_SERVER['QUERY_STRING'] = '/ci/woot?code=good';
        $_SERVER['SCRIPT_NAME']  = '/index.php';

        $_GET['/ci/woot?code'] = 'good';

        $factory = $this->createURIFactory($_SERVER, $_GET);

        $expected = 'ci/woot';
        $this->assertSame($expected, $factory->detectRoutePath('QUERY_STRING'));
        $this->assertSame('code=good', $_SERVER['QUERY_STRING']);
        $this->assertSame(['code' => 'good'], $_GET);
    }

    public function testQueryStringEmpty()
    {
        $_GET = $_SERVER = [];

        // /index.php?
        $_SERVER['REQUEST_URI'] = '/index.php?';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createURIFactory($_SERVER, $_GET);

        $expected = '/';
        $this->assertSame($expected, $factory->detectRoutePath('QUERY_STRING'));
    }

    public function testPathInfoUnset()
    {
        $_GET = $_SERVER = [];

        // /index.php/woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/index.php/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $factory = $this->createURIFactory($_SERVER, $_GET);

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('PATH_INFO'));
    }

    public function testPathInfoSubfolder()
    {
        $_GET = $_SERVER = [];

        $appConfig          = new App();
        $appConfig->baseURL = 'http://localhost:8888/ci431/public/';

        // http://localhost:8888/ci431/public/index.php/woot?code=good#pos
        $_SERVER['PATH_INFO']   = '/woot';
        $_SERVER['REQUEST_URI'] = '/ci431/public/index.php/woot?code=good';
        $_SERVER['SCRIPT_NAME'] = '/ci431/public/index.php';

        $factory = $this->createURIFactory($_SERVER, $_GET, $appConfig);

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('PATH_INFO'));
    }
}
