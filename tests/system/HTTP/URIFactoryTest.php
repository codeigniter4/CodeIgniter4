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
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;

/**
 * @backupGlobals enabled
 *
 * @internal
 *
 * @group Others
 */
final class URIFactoryTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_GET = $_SERVER = [];
    }

    protected function tearDown(): void
    {
        Factories::reset('config');
    }

    public function testCreateCurrentURI()
    {
        // http://localhost:8080/index.php/woot?code=good#pos
        $_SERVER['REQUEST_URI']  = '/index.php/woot?code=good';
        $_SERVER['SCRIPT_NAME']  = '/index.php';
        $_SERVER['QUERY_STRING'] = 'code=good';
        $_SERVER['HTTP_HOST']    = 'localhost:8080';
        $_SERVER['PATH_INFO']    = '/woot';

        $_GET['code'] = 'good';

        $factory = new URIFactory($_SERVER, $_GET, new App());

        $uri = $factory->createFromGlobals();

        $this->assertInstanceOf(URI::class, $uri);
        $this->assertSame('http://localhost:8080/woot?code=good', (string) $uri);
        $this->assertSame('woot', $uri->getPath());
        $this->assertSame('woot', $uri->getRoutePath());
    }
}
