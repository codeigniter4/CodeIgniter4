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
final class IncomingRequestDetectingTest extends CIUnitTestCase
{
    private IncomingRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $_POST = $_GET = $_SERVER = $_REQUEST = $_ENV = $_COOKIE = $_SESSION = [];

        $origin = 'http://www.example.com/index.php/woot?code=good#pos';

        $this->request = new IncomingRequest(new App(), new URI($origin), null, new UserAgent());
    }

    public function testPathDefault()
    {
        $this->request->uri     = '/index.php/woot?code=good#pos';
        $_SERVER['REQUEST_URI'] = '/index.php/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $expected               = 'woot';
        $this->assertSame($expected, $this->request->detectPath());
    }

    public function testPathEmpty()
    {
        $this->request->uri     = '/';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $expected               = '/';
        $this->assertSame($expected, $this->request->detectPath());
    }

    public function testPathRequestURI()
    {
        $this->request->uri     = '/index.php/woot?code=good#pos';
        $_SERVER['REQUEST_URI'] = '/index.php/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $expected               = 'woot';
        $this->assertSame($expected, $this->request->detectPath('REQUEST_URI'));
    }

    public function testPathRequestURINested()
    {
        $this->request->uri     = '/ci/index.php/woot?code=good#pos';
        $_SERVER['REQUEST_URI'] = '/index.php/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $expected               = 'woot';
        $this->assertSame($expected, $this->request->detectPath('REQUEST_URI'));
    }

    public function testPathRequestURISubfolder()
    {
        $this->request->uri     = '/ci/index.php/popcorn/woot?code=good#pos';
        $_SERVER['REQUEST_URI'] = '/ci/index.php/popcorn/woot';
        $_SERVER['SCRIPT_NAME'] = '/ci/index.php';
        $expected               = 'popcorn/woot';
        $this->assertSame($expected, $this->request->detectPath('REQUEST_URI'));
    }

    public function testPathRequestURINoIndex()
    {
        $this->request->uri     = '/sub/example';
        $_SERVER['REQUEST_URI'] = '/sub/example';
        $_SERVER['SCRIPT_NAME'] = '/sub/index.php';
        $expected               = 'example';
        $this->assertSame($expected, $this->request->detectPath('REQUEST_URI'));
    }

    public function testPathRequestURINginx()
    {
        $this->request->uri     = '/ci/index.php/woot?code=good#pos';
        $_SERVER['REQUEST_URI'] = '/index.php/woot?code=good';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $expected               = 'woot';
        $this->assertSame($expected, $this->request->detectPath('REQUEST_URI'));
    }

    public function testPathRequestURINginxRedirecting()
    {
        $this->request->uri     = '/?/ci/index.php/woot';
        $_SERVER['REQUEST_URI'] = '/?/ci/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $expected               = 'ci/woot';
        $this->assertSame($expected, $this->request->detectPath('REQUEST_URI'));
    }

    public function testPathRequestURISuppressed()
    {
        $this->request->uri     = '/woot?code=good#pos';
        $_SERVER['REQUEST_URI'] = '/woot';
        $_SERVER['SCRIPT_NAME'] = '/';
        $expected               = 'woot';
        $this->assertSame($expected, $this->request->detectPath('REQUEST_URI'));
    }

    public function testPathQueryString()
    {
        $this->request->uri      = '/?/ci/index.php/woot';
        $_SERVER['REQUEST_URI']  = '/?/ci/woot';
        $_SERVER['QUERY_STRING'] = '/ci/woot';
        $_SERVER['SCRIPT_NAME']  = '/index.php';
        $expected                = 'ci/woot';
        $this->assertSame($expected, $this->request->detectPath('QUERY_STRING'));
    }

    public function testPathQueryStringEmpty()
    {
        $this->request->uri      = '/?/ci/index.php/woot';
        $_SERVER['REQUEST_URI']  = '/?/ci/woot';
        $_SERVER['QUERY_STRING'] = '';
        $_SERVER['SCRIPT_NAME']  = '/index.php';
        $expected                = '';
        $this->assertSame($expected, $this->request->detectPath('QUERY_STRING'));
    }

    public function testPathPathInfo()
    {
        $this->request->uri = '/index.php/woot?code=good#pos';
        $this->request->setGlobal('server', [
            'PATH_INFO' => null,
        ]);
        $_SERVER['REQUEST_URI'] = '/index.php/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $expected               = 'woot';
        $this->assertSame($expected, $this->request->detectPath('PATH_INFO'));
    }

    public function testPathPathInfoGlobal()
    {
        $this->request->uri = '/index.php/woot?code=good#pos';
        $this->request->setGlobal('server', [
            'PATH_INFO' => 'silliness',
        ]);
        $_SERVER['REQUEST_URI'] = '/index.php/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $expected = 'silliness';
        $this->assertSame($expected, $this->request->detectPath('PATH_INFO'));
    }
}
