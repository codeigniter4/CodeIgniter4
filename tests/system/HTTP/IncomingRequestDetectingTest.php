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

        // The URI object is not used in detectPath().
        $origin        = 'http://www.example.com/index.php/woot?code=good#pos';
        $this->request = new IncomingRequest(new App(), new URI($origin), null, new UserAgent());
    }

    public function testPathDefault()
    {
        // /index.php/woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/index.php/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $expected = 'woot';
        $this->assertSame($expected, $this->request->detectPath());
    }

    public function testPathDefaultEmpty()
    {
        // /
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $expected = '/';
        $this->assertSame($expected, $this->request->detectPath());
    }

    public function testPathRequestURI()
    {
        // /index.php/woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/index.php/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $expected = 'woot';
        $this->assertSame($expected, $this->request->detectPath('REQUEST_URI'));
    }

    public function testPathRequestURINested()
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

        $expected = 'woot';
        $this->assertSame($expected, $this->request->detectPath('REQUEST_URI'));
    }

    public function testPathRequestURISubfolder()
    {
        // /ci/index.php/popcorn/woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/ci/index.php/popcorn/woot';
        $_SERVER['SCRIPT_NAME'] = '/ci/index.php';

        $expected = 'popcorn/woot';
        $this->assertSame($expected, $this->request->detectPath('REQUEST_URI'));
    }

    public function testPathRequestURINoIndex()
    {
        // /sub/example
        $_SERVER['REQUEST_URI'] = '/sub/example';
        $_SERVER['SCRIPT_NAME'] = '/sub/index.php';

        $expected = 'example';
        $this->assertSame($expected, $this->request->detectPath('REQUEST_URI'));
    }

    public function testPathRequestURINginx()
    {
        // /ci/index.php/woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/index.php/woot?code=good';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $expected = 'woot';
        $this->assertSame($expected, $this->request->detectPath('REQUEST_URI'));
    }

    public function testPathRequestURINginxRedirecting()
    {
        // /?/ci/index.php/woot
        $_SERVER['REQUEST_URI'] = '/?/ci/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $expected = 'ci/woot';
        $this->assertSame($expected, $this->request->detectPath('REQUEST_URI'));
    }

    public function testPathRequestURISuppressed()
    {
        // /woot?code=good#pos
        $_SERVER['REQUEST_URI'] = '/woot';
        $_SERVER['SCRIPT_NAME'] = '/';

        $expected = 'woot';
        $this->assertSame($expected, $this->request->detectPath('REQUEST_URI'));
    }

    public function testPathQueryString()
    {
        // /index.php?/ci/woot
        $_SERVER['REQUEST_URI']  = '/index.php?/ci/woot';
        $_SERVER['QUERY_STRING'] = '/ci/woot';
        $_SERVER['SCRIPT_NAME']  = '/index.php';

        $expected = 'ci/woot';
        $this->assertSame($expected, $this->request->detectPath('QUERY_STRING'));
    }

    public function testPathQueryStringWithQueryString()
    {
        // /index.php?/ci/woot?code=good#pos
        $_SERVER['REQUEST_URI']  = '/index.php?/ci/woot?code=good';
        $_SERVER['QUERY_STRING'] = '/ci/woot?code=good';
        $_SERVER['SCRIPT_NAME']  = '/index.php';

        $expected = 'ci/woot';
        $this->assertSame($expected, $this->request->detectPath('QUERY_STRING'));
    }

    public function testPathQueryStringEmpty()
    {
        // /index.php?
        $_SERVER['REQUEST_URI']  = '/index.php?';
        $_SERVER['QUERY_STRING'] = '';
        $_SERVER['SCRIPT_NAME']  = '/index.php';

        $expected = '/';
        $this->assertSame($expected, $this->request->detectPath('QUERY_STRING'));
    }

    public function testPathPathInfo()
    {
        // /index.php/woot?code=good#pos
        $this->request->setGlobal('server', [
            'PATH_INFO' => null,
        ]);
        $_SERVER['REQUEST_URI'] = '/index.php/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $expected = 'woot';
        $this->assertSame($expected, $this->request->detectPath('PATH_INFO'));
    }

    public function testPathPathInfoGlobal()
    {
        // /index.php/woot?code=good#pos
        $this->request->setGlobal('server', [
            'PATH_INFO' => 'silliness',
        ]);
        $_SERVER['REQUEST_URI'] = '/index.php/woot';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $expected = 'silliness';
        $this->assertSame($expected, $this->request->detectPath('PATH_INFO'));
    }
}