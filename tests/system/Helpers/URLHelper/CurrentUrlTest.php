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
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\SiteURIFactory;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Superglobals;
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
 *
 * @group Others
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

        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $_SERVER = [];
    }

    public function testCurrentURLReturnsBasicURL(): void
    {
        $_SERVER['REQUEST_URI'] = '/public/';
        $_SERVER['SCRIPT_NAME'] = '/public/index.php';

        $this->config->baseURL = 'http://example.com/public/';

        $this->createRequest($this->config);

        $this->assertSame('http://example.com/public/index.php/', current_url());
    }

    public function testCurrentURLReturnsAllowedHostname(): void
    {
        $_SERVER['HTTP_HOST']   = 'www.example.jp';
        $_SERVER['REQUEST_URI'] = '/public/';
        $_SERVER['SCRIPT_NAME'] = '/public/index.php';

        $this->config->baseURL          = 'http://example.com/public/';
        $this->config->allowedHostnames = ['www.example.jp'];

        $this->createRequest($this->config);

        $this->assertSame('http://www.example.jp/public/index.php/', current_url());
    }

    private function createRequest(?App $config = null, $body = null, ?string $path = null): void
    {
        $config ??= new App();

        $factory = new SiteURIFactory($config, new Superglobals());
        $uri     = $factory->createFromGlobals();

        if ($path !== null) {
            $uri->setPath($path);
        }

        $request = new IncomingRequest($config, $uri, $body, new UserAgent());
        Services::injectMock('request', $request);

        Factories::injectMock('config', 'App', $config);
    }

    public function testCurrentURLReturnsBaseURLIfNotAllowedHostname(): void
    {
        $_SERVER['HTTP_HOST']   = 'invalid.example.org';
        $_SERVER['REQUEST_URI'] = '/public/';
        $_SERVER['SCRIPT_NAME'] = '/public/index.php';

        $this->config->baseURL          = 'http://example.com/public/';
        $this->config->allowedHostnames = ['www.example.jp'];

        $this->createRequest($this->config);

        $this->assertSame('http://example.com/public/index.php/', current_url());
    }

    public function testCurrentURLReturnsObject(): void
    {
        $this->config->baseURL = 'http://example.com/public/';

        $this->createRequest($this->config);

        $url = current_url(true);

        $this->assertInstanceOf(URI::class, $url);
        $this->assertSame('http://example.com/public/index.php/', (string) $url);
    }

    public function testCurrentURLEquivalence(): void
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/public/';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $this->config->indexPage = '';

        $this->createRequest($this->config);

        $this->assertSame(site_url(uri_string()), current_url());
    }

    public function testCurrentURLInSubfolder(): void
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/foo/public/bar?baz=quip';
        $_SERVER['SCRIPT_NAME'] = '/foo/public/index.php';

        $this->config->baseURL = 'http://example.com/foo/public/';

        $this->createRequest($this->config);

        $this->assertSame('http://example.com/foo/public/index.php/bar', current_url());
        $this->assertSame('http://example.com/foo/public/index.php/bar?baz=quip', (string) current_url(true));

        $uri = current_url(true);
        $this->assertSame('bar', $uri->getSegment(1));
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame('http', $uri->getScheme());
    }

    public function testCurrentURLWithPortInSubfolder(): void
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['SERVER_PORT'] = '8080';
        $_SERVER['REQUEST_URI'] = '/foo/public/bar?baz=quip';
        $_SERVER['SCRIPT_NAME'] = '/foo/public/index.php';

        $this->config->baseURL = 'http://example.com:8080/foo/public/';

        $this->createRequest($this->config);

        $this->assertSame('http://example.com:8080/foo/public/index.php/bar', current_url());
        $this->assertSame('http://example.com:8080/foo/public/index.php/bar?baz=quip', (string) current_url(true));

        $uri = current_url(true);
        $this->assertSame(['bar'], $uri->getSegments());
        $this->assertSame('bar', $uri->getSegment(1));
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame('http', $uri->getScheme());
        $this->assertSame(8080, $uri->getPort());
    }

    public function testUriString(): void
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/assets/image.jpg';

        $this->config->indexPage = '';

        $this->createRequest($this->config);

        $this->assertSame('assets/image.jpg', uri_string());
    }

    public function testUriStringNoTrailingSlash(): void
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/assets/image.jpg';

        $this->config->baseURL   = 'http://example.com/';
        $this->config->indexPage = '';

        $this->createRequest($this->config);

        $this->assertSame('assets/image.jpg', uri_string());
    }

    public function testUriStringEmpty(): void
    {
        $this->createRequest($this->config);

        $this->assertSame('', uri_string());
    }

    public function testUriStringSubfolderAbsolute(): void
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/subfolder/assets/image.jpg';

        $this->config->baseURL = 'http://example.com/subfolder/';

        $this->createRequest($this->config);

        $this->assertSame('subfolder/assets/image.jpg', uri_string());
    }

    public function testUriStringSubfolderRelative(): void
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/subfolder/assets/image.jpg';
        $_SERVER['SCRIPT_NAME'] = '/subfolder/index.php';

        $this->config->baseURL = 'http://example.com/subfolder/';

        $this->createRequest($this->config);

        $this->assertSame('assets/image.jpg', uri_string());
    }

    public static function provideUrlIs(): iterable
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
     * @dataProvider provideUrlIs
     */
    public function testUrlIs(string $currentPath, string $testPath, bool $expected): void
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/' . $currentPath;

        $this->createRequest($this->config);

        $this->assertSame($expected, url_is($testPath));
    }

    /**
     * @dataProvider provideUrlIs
     */
    public function testUrlIsNoIndex(string $currentPath, string $testPath, bool $expected): void
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/' . $currentPath;

        $this->config->indexPage = '';

        $this->createRequest($this->config);

        $this->assertSame($expected, url_is($testPath));
    }

    /**
     * @dataProvider provideUrlIs
     */
    public function testUrlIsWithSubfolder(string $currentPath, string $testPath, bool $expected): void
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/' . $currentPath;
        $_SERVER['SCRIPT_NAME'] = '/subfolder/index.php';

        $this->config->baseURL = 'http://example.com/subfolder/';

        $this->createRequest($this->config);

        $this->assertSame($expected, url_is($testPath));
    }
}
