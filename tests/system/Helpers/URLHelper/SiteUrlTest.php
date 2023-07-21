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
 * Since base_url() only slightly modifies
 * site_url() these functions are tested
 * simultaneously.
 *
 * @backupGlobals enabled
 *
 * @internal
 *
 * @group Others
 */
final class SiteUrlTest extends CIUnitTestCase
{
    private App $config;

    protected function setUp(): void
    {
        parent::setUp();

        Services::reset(true);

        $this->config = new App();
        Factories::injectMock('config', 'App', $this->config);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $_SERVER = [];
    }

    /**
     * Takes a multitude of various config input and verifies
     * that base_url() and site_url() return the expected result.
     *
     * @param string      $baseURL
     * @param string      $indexPage
     * @param string|null $scheme
     * @param bool        $secure
     * @param string      $path
     * @param string      $expectedSiteUrl
     * @param string      $expectedBaseUrl
     *
     * @dataProvider configProvider
     */
    public function testUrls(
        $baseURL,
        $indexPage,
        $scheme,
        $secure,
        $path,
        $expectedSiteUrl,
        $expectedBaseUrl
    ) {
        // Set the config
        $this->config->baseURL                   = $baseURL;
        $this->config->indexPage                 = $indexPage;
        $this->config->forceGlobalSecureRequests = $secure;

        $this->assertSame($expectedSiteUrl, site_url($path, $scheme, $this->config));
        $this->assertSame($expectedBaseUrl, base_url($path, $scheme));
    }

    public function configProvider(): iterable
    {
        // baseURL, indexPage, scheme, secure, path, expectedSiteUrl, expectedBaseUrl
        return [
            'forceGlobalSecure' => [
                'http://example.com/',
                'index.php',
                null,
                true,
                '',
                'https://example.com/index.php',
                'https://example.com/',
            ],
            [
                'http://example.com/',
                'index.php',
                null,
                false,
                '',
                'http://example.com/index.php',
                'http://example.com/',
            ],
            'baseURL missing /' => [
                'http://example.com',
                'index.php',
                null,
                false,
                '',
                'http://example.com/index.php',
                'http://example.com/',
            ],
            [
                'http://example.com/',
                '',
                null,
                false,
                '',
                'http://example.com/',
                'http://example.com/',
            ],
            [
                'http://example.com/',
                'banana.php',
                null,
                false,
                '',
                'http://example.com/banana.php',
                'http://example.com/',
            ],
            [
                'http://example.com/',
                '',
                null,
                false,
                'abc',
                'http://example.com/abc',
                'http://example.com/abc',
            ],
            [
                'http://example.com/',
                '',
                null,
                false,
                '/abc',
                'http://example.com/abc',
                'http://example.com/abc',
            ],
            [
                'http://example.com/',
                '',
                null,
                false,
                '/abc/',
                'http://example.com/abc/',
                'http://example.com/abc/',
            ],
            [
                'http://example.com/',
                '',
                null,
                false,
                '/abc/def',
                'http://example.com/abc/def',
                'http://example.com/abc/def',
            ],
            'URL decode' => [
                'http://example.com/',
                '',
                null,
                false,
                'template/meet-%26-greet',
                'http://example.com/template/meet-&-greet',
                'http://example.com/template/meet-&-greet',
            ],
            'URL encode' => [
                'http://example.com/',
                '',
                null,
                false,
                '<s>alert</s>',
                'http://example.com/%3Cs%3Ealert%3C/s%3E',
                'http://example.com/%3Cs%3Ealert%3C/s%3E',
            ],
            [
                'http://example.com/public/',
                'index.php',
                null,
                false,
                '',
                'http://example.com/public/index.php',
                'http://example.com/public/',
            ],
            [
                'http://example.com/public/',
                '',
                null,
                false,
                '',
                'http://example.com/public/',
                'http://example.com/public/',
            ],
            [
                'http://example.com/public',
                '',
                null,
                false,
                '',
                'http://example.com/public/',
                'http://example.com/public/',
            ],
            [
                'http://example.com/public',
                'index.php',
                null,
                false,
                '/',
                'http://example.com/public/index.php/',
                'http://example.com/public/',
            ],
            [
                'http://example.com/public/',
                'index.php',
                null,
                false,
                '/',
                'http://example.com/public/index.php/',
                'http://example.com/public/',
            ],
            [
                'http://example.com/',
                'index.php',
                null,
                false,
                'foo',
                'http://example.com/index.php/foo',
                'http://example.com/foo',
            ],
            [
                'http://example.com/',
                'index.php',
                null,
                false,
                '0',
                'http://example.com/index.php/0',
                'http://example.com/0',
            ],
            [
                'http://example.com/public',
                'index.php',
                null,
                false,
                'foo',
                'http://example.com/public/index.php/foo',
                'http://example.com/public/foo',
            ],
            [
                'http://example.com/',
                'index.php',
                null,
                false,
                'foo?bar=bam',
                'http://example.com/index.php/foo?bar=bam',
                'http://example.com/foo?bar=bam',
            ],
            [
                'http://example.com/',
                'index.php',
                null,
                false,
                'test#banana',
                'http://example.com/index.php/test#banana',
                'http://example.com/test#banana',
            ],
            [
                'http://example.com/',
                'index.php',
                'ftp',
                false,
                'foo',
                'ftp://example.com/index.php/foo',
                'ftp://example.com/foo',
            ],
            [
                'http://example.com/',
                'index.php',
                null,
                false,
                'news/local/123',
                'http://example.com/index.php/news/local/123',
                'http://example.com/news/local/123',
            ],
            [
                'http://example.com/',
                'index.php',
                null,
                false,
                ['news', 'local', '123'],
                'http://example.com/index.php/news/local/123',
                'http://example.com/news/local/123',
            ],
        ];
    }

    public function testSiteURLWithEmptyStringScheme()
    {
        $this->config->baseURL                   = 'http://example.com/';
        $this->config->indexPage                 = 'index.php';
        $this->config->forceGlobalSecureRequests = false;

        $this->assertSame(
            '//example.com/index.php/test',
            site_url('test', '', $this->config)
        );
        $this->assertSame(
            '//example.com/img/test.jpg',
            base_url('img/test.jpg', '')
        );
    }

    /**
     * These tests are only really relevant to show that base_url()
     * has no interaction with the current request URI.
     *
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/240
     */
    public function testBaseURLDiscovery()
    {
        $this->config->baseURL = 'http://example.com/';

        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/test';

        $this->assertSame('http://example.com/', base_url());

        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/test/page';

        $this->assertSame('http://example.com/', base_url());
        $this->assertSame('http://example.com/profile', base_url('profile'));
    }

    public function testBaseURLService()
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/ci/v4/x/y';

        $this->config->baseURL = 'http://example.com/ci/v4/';
        $request               = Services::request($this->config);
        Services::injectMock('request', $request);

        $this->assertSame('http://example.com/ci/v4/index.php/controller/method', site_url('controller/method', null, $this->config));
        $this->assertSame('http://example.com/ci/v4/controller/method', base_url('controller/method', null));
    }

    public function testBaseURLWithCLIRequest()
    {
        unset($_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);

        $this->config->baseURL = 'http://example.com/';
        $request               = Services::clirequest($this->config);
        Services::injectMock('request', $request);

        $this->assertSame(
            'http://example.com/index.php/controller/method',
            site_url('controller/method', null, $this->config)
        );
        $this->assertSame(
            'http://example.com/controller/method',
            base_url('controller/method', null)
        );
    }

    public function testSiteURLWithAllowedHostname()
    {
        $_SERVER['HTTP_HOST']   = 'www.example.jp';
        $_SERVER['REQUEST_URI'] = '/public';
        $_SERVER['SCRIPT_NAME'] = '/public/index.php';

        $this->config->baseURL          = 'http://example.com/public/';
        $this->config->allowedHostnames = ['www.example.jp'];
        Services::injectMock('config', $this->config);

        // URI object are updated in IncomingRequest constructor.
        $request = Services::incomingrequest($this->config);
        Services::injectMock('request', $request);

        $this->assertSame(
            'http://www.example.jp/public/index.php/controller/method',
            site_url('controller/method')
        );
    }

    public function testSiteURLWithAltConfig()
    {
        $_SERVER['HTTP_HOST']   = 'www.example.jp';
        $_SERVER['REQUEST_URI'] = '/public';
        $_SERVER['SCRIPT_NAME'] = '/public/index.php';

        $this->config->baseURL          = 'http://example.com/public/';
        $this->config->allowedHostnames = ['www.example.jp'];

        // URI object are updated in IncomingRequest constructor.
        $request = Services::incomingrequest($this->config);
        Services::injectMock('request', $request);

        $altConfig          = clone $this->config;
        $altConfig->baseURL = 'http://alt.example.com/public/';

        $this->assertSame(
            'http://alt.example.com/public/index.php/controller/method',
            site_url('controller/method', null, $altConfig)
        );
    }

    public function testBaseURLWithAllowedHostname()
    {
        $_SERVER['HTTP_HOST']   = 'www.example.jp';
        $_SERVER['REQUEST_URI'] = '/public';
        $_SERVER['SCRIPT_NAME'] = '/public/index.php';

        $this->config->baseURL          = 'http://example.com/public/';
        $this->config->allowedHostnames = ['www.example.jp'];

        // URI object are updated in IncomingRequest constructor.
        $request = Services::incomingrequest($this->config);
        Services::injectMock('request', $request);

        $this->assertSame(
            'http://www.example.jp/public/controller/method',
            base_url('controller/method', null)
        );
    }
}
