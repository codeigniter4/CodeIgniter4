<?php

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
 */
final class SiteUrlTest extends CIUnitTestCase
{
    /**
     * @var App
     */
    private $config;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        helper('url');
    }

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

    //--------------------------------------------------------------------

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
     *
     * @dataProvider configProvider
     */
    public function testUrls($baseURL, $indexPage, $scheme, $secure, $path, $expectedSiteUrl)
    {
        // Set the config
        $this->config->baseURL                   = $baseURL;
        $this->config->indexPage                 = $indexPage;
        $this->config->forceGlobalSecureRequests = $secure;

        $this->assertSame($expectedSiteUrl, site_url($path, $scheme, $this->config));

        // base_url is always the trimmed site_url without index page
        $expectedBaseUrl = $indexPage === '' ? $expectedSiteUrl : str_replace('/' . $indexPage, '', $expectedSiteUrl);
        $expectedBaseUrl = rtrim($expectedBaseUrl, '/');
        $this->assertSame($expectedBaseUrl, base_url($path, $scheme));
    }

    public function configProvider()
    {
        // baseURL, indexPage, scheme, path, expectedSiteUrl
        return [
            [
                'http://example.com/',
                'index.php',
                null,
                false,
                '',
                'http://example.com/index.php',
            ],
            [
                'http://example.com',
                'index.php',
                null,
                false,
                '',
                'http://example.com/index.php',
            ],
            [
                'http://example.com/',
                '',
                null,
                false,
                '',
                'http://example.com/',
            ],
            [
                'http://example.com/',
                'banana.php',
                null,
                false,
                '',
                'http://example.com/banana.php',
            ],
            [
                'http://example.com/',
                '',
                null,
                false,
                'abc',
                'http://example.com/abc',
            ],
            [
                'http://example.com/public/',
                'index.php',
                null,
                false,
                '',
                'http://example.com/public/index.php',
            ],
            [
                'http://example.com/public/',
                '',
                null,
                false,
                '',
                'http://example.com/public/',
            ],
            [
                'http://example.com/public',
                '',
                null,
                false,
                '',
                'http://example.com/public/',
            ],
            [
                'http://example.com/public',
                'index.php',
                null,
                false,
                '/',
                'http://example.com/public/index.php/',
            ],
            [
                'http://example.com/public/',
                'index.php',
                null,
                false,
                '/',
                'http://example.com/public/index.php/',
            ],
            [
                'http://example.com/',
                'index.php',
                null,
                false,
                'foo',
                'http://example.com/index.php/foo',
            ],
            [
                'http://example.com/',
                'index.php',
                null,
                false,
                '0',
                'http://example.com/index.php/0',
            ],
            [
                'http://example.com/public',
                'index.php',
                null,
                false,
                'foo',
                'http://example.com/public/index.php/foo',
            ],
            [
                'http://example.com/',
                'index.php',
                null,
                false,
                'foo?bar=bam',
                'http://example.com/index.php/foo?bar=bam',
            ],
            [
                'http://example.com/',
                'index.php',
                null,
                false,
                'test#banana',
                'http://example.com/index.php/test#banana',
            ],
            [
                'http://example.com/',
                'index.php',
                'ftp',
                false,
                'foo',
                'ftp://example.com/index.php/foo',
            ],
            [
                'http://example.com/',
                'index.php',
                null,
                false,
                'news/local/123',
                'http://example.com/index.php/news/local/123',
            ],
            [
                'http://example.com/',
                'index.php',
                null,
                false,
                ['news', 'local', '123'],
                'http://example.com/index.php/news/local/123',
            ],
        ];
    }

    //--------------------------------------------------------------------
    // base_url
    //--------------------------------------------------------------------

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

        $this->assertSame('http://example.com', base_url());

        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/test/page';

        $this->assertSame('http://example.com', base_url());
        $this->assertSame('http://example.com/profile', base_url('profile'));
    }

    public function testBaseURLService()
    {
        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['REQUEST_URI'] = '/ci/v4/x/y';

        $this->config->baseURL = 'http://example.com/ci/v4/';
        $request               = Services::request($this->config);
        $request->uri          = new URI('http://example.com/ci/v4/x/y');

        Services::injectMock('request', $request);

        $this->assertSame('http://example.com/ci/v4/index.php/controller/method', site_url('controller/method', null, $this->config));
        $this->assertSame('http://example.com/ci/v4/controller/method', base_url('controller/method', null));
    }
}
