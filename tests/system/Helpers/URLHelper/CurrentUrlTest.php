<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Helpers\URLHelper;

use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;

/**
 * Test cases for all URL Helper functions
 * that rely on the "current" URL.
 * Includes: current_url, uri_string, uri_is
 *
 * @backupGlobals enabled
 *
 * @internal
 */
final class CurrentUrlTest extends CIUnitTestCase
{
    private App $config;

    protected function setUp(): void
    {
        parent::setUp();

        Services::reset(true);

        // Set a common base configuration (overriden by individual tests)
        $this->config            = new App();
        $this->config->baseURL   = 'http://example.com/';
        $this->config->indexPage = 'index.php';
        Factories::injectMock('config', 'App', $this->config);

        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $_SERVER = [];
    }

    public function testCurrentURLReturnsBasicURL()
    {
        // Since we're on a CLI, we must provide our own URI
        $this->config->baseURL = 'http://example.com/public';

        $this->assertSame('http://example.com/public/index.php/', current_url());
    }

    public function testCurrentURLReturnsObject()
    {
        // Since we're on a CLI, we must provide our own URI
        $this->config->baseURL = 'http://example.com/public';

        $url = current_url(true);

        $this->assertInstanceOf(URI::class, $url);
        $this->assertSame('http://example.com/public/index.php/', (string) $url);
    }

    public function testCurrentURLEquivalence()
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/public';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        // Since we're on a CLI, we must provide our own URI
        Factories::injectMock('config', 'App', $this->config);

        $request = Services::request($this->config);
        Services::injectMock('request', $request);

        $this->assertSame(site_url(uri_string()), current_url());
    }

    public function testCurrentURLInSubfolder()
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/foo/public/bar?baz=quip';
        $_SERVER['SCRIPT_NAME'] = '/foo/public/index.php';

        // Since we're on a CLI, we must provide our own URI
        $this->config->baseURL = 'http://example.com/foo/public';
        Factories::injectMock('config', 'App', $this->config);

        $request = Services::request($this->config);
        Services::injectMock('request', $request);

        $this->assertSame('http://example.com/foo/public/index.php/bar', current_url());
        $this->assertSame('http://example.com/foo/public/index.php/bar?baz=quip', (string) current_url(true));

        $uri = current_url(true);
        $this->assertSame('foo', $uri->getSegment(1));
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame('http', $uri->getScheme());
    }

    public function testCurrentURLWithPortInSubfolder()
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['SERVER_PORT'] = '8080';
        $_SERVER['REQUEST_URI'] = '/foo/public/bar?baz=quip';
        $_SERVER['SCRIPT_NAME'] = '/foo/public/index.php';

        // Since we're on a CLI, we must provide our own URI
        $this->config->baseURL = 'http://example.com:8080/foo/public';
        Factories::injectMock('config', 'App', $this->config);

        $request = Services::request($this->config);
        Services::injectMock('request', $request);

        $this->assertSame('http://example.com:8080/foo/public/index.php/bar', current_url());
        $this->assertSame('http://example.com:8080/foo/public/index.php/bar?baz=quip', (string) current_url(true));

        $uri = current_url(true);
        $this->assertSame(['foo', 'public', 'index.php', 'bar'], $uri->getSegments());
        $this->assertSame('foo', $uri->getSegment(1));
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame('http', $uri->getScheme());
        $this->assertSame(8080, $uri->getPort());
    }

    public function testUriStringAbsolute()
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/assets/image.jpg';

        $uri = 'http://example.com/assets/image.jpg';
        $this->setService($uri);

        $this->assertSame('assets/image.jpg', uri_string());
    }

    private function setService(string $uri): void
    {
        $uri = new URI($uri);
        Services::injectMock('uri', $uri);

        $request = Services::request($this->config);
        Services::injectMock('request', $request);
    }

    public function testUriStringRelative()
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/assets/image.jpg';

        $uri = 'http://example.com/assets/image.jpg';
        $this->setService($uri);

        $this->assertSame('assets/image.jpg', uri_string(true));
    }

    public function testUriStringNoTrailingSlashAbsolute()
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/assets/image.jpg';

        $this->config->baseURL = 'http://example.com';

        $uri = 'http://example.com/assets/image.jpg';
        $this->setService($uri);

        $this->assertSame('assets/image.jpg', uri_string());
    }

    public function testUriStringNoTrailingSlashRelative()
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/assets/image.jpg';

        $this->config->baseURL = 'http://example.com';

        $uri = 'http://example.com/assets/image.jpg';
        $this->setService($uri);

        $this->assertSame('assets/image.jpg', uri_string(true));
    }

    public function testUriStringEmptyAbsolute()
    {
        $uri = 'http://example.com/';
        $this->setService($uri);

        $this->assertSame('/', uri_string());
    }

    public function testUriStringEmptyRelative()
    {
        $uri = 'http://example.com/';
        $this->setService($uri);

        $this->assertSame('', uri_string(true));
    }

    public function testUriStringSubfolderAbsolute()
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/subfolder/assets/image.jpg';

        $this->config->baseURL = 'http://example.com/subfolder/';

        $uri = 'http://example.com/subfolder/assets/image.jpg';
        $this->setService($uri);

        $this->assertSame('subfolder/assets/image.jpg', uri_string());
    }

    public function testUriStringSubfolderRelative()
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/subfolder/assets/image.jpg';
        $_SERVER['SCRIPT_NAME'] = '/subfolder/index.php';

        $this->config->baseURL = 'http://example.com/subfolder/';

        $uri = 'http://example.com/subfolder/assets/image.jpg';
        $this->setService($uri);

        $this->assertSame('assets/image.jpg', uri_string(true));
    }

    public function urlIsProvider()
    {
        return [
            [
                'foo/bar',
                'foo/bar',
                true,
            ],
            [
                'foo/bar',
                'foo*',
                true,
            ],
            [
                'foo/bar',
                'foo',
                false,
            ],
            [
                'foo/bar',
                'baz/foo/bar',
                false,
            ],
            [
                '',
                'foo*',
                false,
            ],
            [
                'foo/',
                'foo*',
                true,
            ],
            [
                'foo/',
                'foo',
                true,
            ],
        ];
    }

    /**
     * @dataProvider urlIsProvider
     */
    public function testUrlIs(string $currentPath, string $testPath, bool $expected)
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/' . $currentPath;

        $uri = 'http://example.com/' . $currentPath;
        $this->setService($uri);

        $this->assertSame($expected, url_is($testPath));
    }

    /**
     * @dataProvider urlIsProvider
     */
    public function testUrlIsNoIndex(string $currentPath, string $testPath, bool $expected)
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/' . $currentPath;

        $this->config->indexPage = '';

        $uri = 'http://example.com/' . $currentPath;
        $this->setService($uri);

        $this->assertSame($expected, url_is($testPath));
    }

    /**
     * @dataProvider urlIsProvider
     */
    public function testUrlIsWithSubfolder(string $currentPath, string $testPath, bool $expected)
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/' . $currentPath;
        $_SERVER['SCRIPT_NAME'] = '/subfolder/index.php';

        $this->config->baseURL = 'http://example.com/subfolder/';

        $uri = 'http://example.com/subfolder/' . $currentPath;
        $this->setService($uri);

        $this->assertSame($expected, url_is($testPath));
    }
}
