<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use CodeIgniter\Config\Services;
use CodeIgniter\Superglobals;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[BackupGlobals(true)]
#[Group('Others')]
final class SiteURIFactoryDetectRoutePathTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_GET = $_SERVER = [];

        Services::injectMock('superglobals', new Superglobals());
    }

    private function createSiteURIFactory(array $server, ?App $appConfig = null): SiteURIFactory
    {
        $appConfig ??= new App();

        $superglobals = new Superglobals($server);

        return new SiteURIFactory($appConfig, $superglobals);
    }

    public function testDefault(): void
    {
        // /index.php/woot?code=good#pos
        service('superglobals')
            ->setServer('REQUEST_URI', '/index.php/woot')
            ->setServer('SCRIPT_NAME', '/index.php');

        $factory = $this->createSiteURIFactory(service('superglobals')->getServerArray());

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath());
    }

    public function testDefaultEmpty(): void
    {
        // /
        service('superglobals')
            ->setServer('REQUEST_URI', '/')
            ->setServer('SCRIPT_NAME', '/index.php');

        $factory = $this->createSiteURIFactory(service('superglobals')->getServerArray());

        $expected = '/';
        $this->assertSame($expected, $factory->detectRoutePath());
    }

    public function testRequestURI(): void
    {
        // /index.php/woot?code=good#pos
        service('superglobals')
            ->setServer('REQUEST_URI', '/index.php/woot')
            ->setServer('SCRIPT_NAME', '/index.php');

        $factory = $this->createSiteURIFactory(service('superglobals')->getServerArray());

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURINested(): void
    {
        // I'm not sure but this is a case of Apache config making such SERVER
        // values?
        // The current implementation doesn't use the value of the URI object.
        // So I removed the code to set URI. Therefore, it's exactly the same as
        // the method above as a test.
        // But it may be changed in the future to use the value of the URI object.
        // So I don't remove this test case.

        // /ci/index.php/woot?code=good#pos
        service('superglobals')
            ->setServer('REQUEST_URI', '/index.php/woot')
            ->setServer('SCRIPT_NAME', '/index.php');

        $factory = $this->createSiteURIFactory(service('superglobals')->getServerArray());

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURISubfolder(): void
    {
        // /ci/index.php/popcorn/woot?code=good#pos
        service('superglobals')
            ->setServer('REQUEST_URI', '/ci/index.php/popcorn/woot')
            ->setServer('SCRIPT_NAME', '/ci/index.php');

        $factory = $this->createSiteURIFactory(service('superglobals')->getServerArray());

        $expected = 'popcorn/woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURINoIndex(): void
    {
        // /sub/example
        service('superglobals')
            ->setServer('REQUEST_URI', '/sub/example')
            ->setServer('SCRIPT_NAME', '/sub/index.php');

        $factory = $this->createSiteURIFactory(service('superglobals')->getServerArray());

        $expected = 'example';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURINginx(): void
    {
        // /ci/index.php/woot?code=good#pos
        service('superglobals')
            ->setServer('REQUEST_URI', '/index.php/woot?code=good')
            ->setServer('SCRIPT_NAME', '/index.php');

        $factory = $this->createSiteURIFactory(service('superglobals')->getServerArray());

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURINginxRedirecting(): void
    {
        // /?/ci/index.php/woot
        service('superglobals')
            ->setServer('REQUEST_URI', '/?/ci/woot')
            ->setServer('SCRIPT_NAME', '/index.php');

        $factory = $this->createSiteURIFactory(service('superglobals')->getServerArray());

        $expected = 'ci/woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURISuppressed(): void
    {
        // /woot?code=good#pos
        service('superglobals')
            ->setServer('REQUEST_URI', '/woot')
            ->setServer('SCRIPT_NAME', '/');

        $factory = $this->createSiteURIFactory(service('superglobals')->getServerArray());

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURIGetPath(): void
    {
        // /index.php/fruits/banana
        service('superglobals')
            ->setServer('REQUEST_URI', '/index.php/fruits/banana')
            ->setServer('SCRIPT_NAME', '/index.php');

        $factory = $this->createSiteURIFactory(service('superglobals')->getServerArray());

        $this->assertSame('fruits/banana', $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURIPathIsRelative(): void
    {
        // /sub/folder/index.php/fruits/banana
        service('superglobals')
            ->setServer('REQUEST_URI', '/sub/folder/index.php/fruits/banana')
            ->setServer('SCRIPT_NAME', '/sub/folder/index.php');

        $factory = $this->createSiteURIFactory(service('superglobals')->getServerArray());

        $this->assertSame('fruits/banana', $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURIStoresDetectedPath(): void
    {
        // /fruits/banana
        service('superglobals')
            ->setServer('REQUEST_URI', '/fruits/banana')
            ->setServer('SCRIPT_NAME', '/index.php');

        $factory = $this->createSiteURIFactory(service('superglobals')->getServerArray());

        service('superglobals')->setServer('REQUEST_URI', '/candy/snickers');

        $this->assertSame('fruits/banana', $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testRequestURIPathIsNeverRediscovered(): void
    {
        service('superglobals')
            ->setServer('REQUEST_URI', '/fruits/banana')
            ->setServer('SCRIPT_NAME', '/index.php');

        $factory = $this->createSiteURIFactory(service('superglobals')->getServerArray());

        service('superglobals')->setServer('REQUEST_URI', '/candy/snickers');
        $factory->detectRoutePath('REQUEST_URI');

        $this->assertSame('fruits/banana', $factory->detectRoutePath('REQUEST_URI'));
    }

    public function testQueryString(): void
    {
        // /index.php?/ci/woot
        service('superglobals')
            ->setServer('REQUEST_URI', '/index.php?/ci/woot')
            ->setServer('QUERY_STRING', '/ci/woot')
            ->setServer('SCRIPT_NAME', '/index.php')
            ->setGet('/ci/woot', '');

        $factory = $this->createSiteURIFactory(service('superglobals')->getServerArray());

        $expected = 'ci/woot';
        $this->assertSame($expected, $factory->detectRoutePath('QUERY_STRING'));
    }

    public function testQueryStringWithQueryString(): void
    {
        // /index.php?/ci/woot?code=good#pos
        service('superglobals')
            ->setServer('REQUEST_URI', '/index.php?/ci/woot?code=good')
            ->setServer('QUERY_STRING', '/ci/woot?code=good')
            ->setServer('SCRIPT_NAME', '/index.php')
            ->setGet('/ci/woot?code', 'good');

        $factory = $this->createSiteURIFactory(service('superglobals')->getServerArray());

        $expected = 'ci/woot';
        $this->assertSame($expected, $factory->detectRoutePath('QUERY_STRING'));
        $this->assertSame('code=good', $_SERVER['QUERY_STRING']);
        $this->assertSame(['code' => 'good'], $_GET);
    }

    public function testQueryStringEmpty(): void
    {
        // /index.php?
        service('superglobals')
            ->setServer('REQUEST_URI', '/index.php?')
            ->setServer('SCRIPT_NAME', '/index.php');

        $factory = $this->createSiteURIFactory(service('superglobals')->getServerArray());

        $expected = '/';
        $this->assertSame($expected, $factory->detectRoutePath('QUERY_STRING'));
    }

    public function testPathInfoUnset(): void
    {
        // /index.php/woot?code=good#pos
        service('superglobals')
            ->setServer('REQUEST_URI', '/index.php/woot')
            ->setServer('SCRIPT_NAME', '/index.php');

        $factory = $this->createSiteURIFactory(service('superglobals')->getServerArray());

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('PATH_INFO'));
    }

    public function testPathInfoSubfolder(): void
    {
        $appConfig          = new App();
        $appConfig->baseURL = 'http://localhost:8888/ci431/public/';

        // http://localhost:8888/ci431/public/index.php/woot?code=good#pos
        service('superglobals')
            ->setServer('PATH_INFO', '/woot')
            ->setServer('REQUEST_URI', '/ci431/public/index.php/woot?code=good')
            ->setServer('SCRIPT_NAME', '/ci431/public/index.php');

        $factory = $this->createSiteURIFactory(service('superglobals')->getServerArray(), $appConfig);

        $expected = 'woot';
        $this->assertSame($expected, $factory->detectRoutePath('PATH_INFO'));
    }

    /**
     * @param string $path
     * @param string $detectPath
     */
    #[DataProvider('provideExtensionPHP')]
    public function testExtensionPHP($path, $detectPath): void
    {
        $config          = new App();
        $config->baseURL = 'http://example.com/';

        service('superglobals')->setServer('REQUEST_URI', $path);
        service('superglobals')->setServer('SCRIPT_NAME', $path);

        $factory = $this->createSiteURIFactory(service('superglobals')->getServerArray(), $config);

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

    #[DataProvider('provideRequestURIRewrite')]
    public function testRequestURIRewrite(
        string $requestUri,
        string $scriptName,
        string $indexPage,
        string $expected,
    ): void {
        $server                = [];
        $server['REQUEST_URI'] = $requestUri;
        $server['SCRIPT_NAME'] = $scriptName;

        $appConfig            = new App();
        $appConfig->indexPage = $indexPage;

        $factory = $this->createSiteURIFactory($server, $appConfig);

        $this->assertSame($expected, $factory->detectRoutePath('REQUEST_URI'));
    }

    /**
     * @return iterable<string, array{
     *     requestUri: string,
     *     scriptName: string,
     *     indexPage: string,
     *     expected: string
     * }>
     */
    public static function provideRequestURIRewrite(): iterable
    {
        return [
            'rewrite_with_route' => [
                'requestUri' => '/ci/index.php/sample/method',
                'scriptName' => '/ci/public/index.php',
                'indexPage'  => 'index.php',
                'expected'   => 'sample/method',
            ],
            'rewrite_root' => [
                'requestUri' => '/ci/index.php',
                'scriptName' => '/ci/public/index.php',
                'indexPage'  => 'index.php',
                'expected'   => '/',
            ],
            'rewrite_no_index_page' => [
                'requestUri' => '/ci/sample/method',
                'scriptName' => '/ci/public/index.php',
                'indexPage'  => '',
                'expected'   => 'sample/method',
            ],
            'rewrite_nested_subfolder' => [
                'requestUri' => '/projects/index.php/api/users/list',
                'scriptName' => '/projects/myapp/public/index.php',
                'indexPage'  => 'index.php',
                'expected'   => 'api/users/list',
            ],
            'rewrite_multiple_public_folders' => [
                'requestUri' => '/public-sites/myapp/index.php/content/view',
                'scriptName' => '/public-sites/myapp/public/index.php',
                'indexPage'  => 'index.php',
                'expected'   => 'content/view',
            ],
            'rewrite_custom_app_folder' => [
                'requestUri' => '/myapp/index.php/products/category/electronics',
                'scriptName' => '/myapp/web/index.php',
                'indexPage'  => 'index.php',
                'expected'   => 'products/category/electronics',
            ],
            'multiple_index_php_in_path' => [
                'requestUri' => '/app/index.php/user/index.php/profile',
                'scriptName' => '/app/public/index.php',
                'indexPage'  => 'index.php',
                'expected'   => 'user/index.php/profile',
            ],
            'custom_index_page_name' => [
                'requestUri' => '/ci/app.php/users/list',
                'scriptName' => '/ci/public/app.php',
                'indexPage'  => 'app.php',
                'expected'   => 'users/list',
            ],
            'custom_index_page_root' => [
                'requestUri' => '/project/main.php',
                'scriptName' => '/project/web/main.php',
                'indexPage'  => 'main.php',
                'expected'   => '/',
            ],
            'partial_match_should_not_remove' => [
                'requestUri' => '/app/myindex.php/route',
                'scriptName' => '/app/public/index.php',
                'indexPage'  => 'index.php',
                'expected'   => 'myindex.php/route',
            ],
            'multibyte_characters' => [
                'requestUri' => '/%ED%85%8C%EC%8A%A4%ED%8A%B81/index.php/route',
                'scriptName' => '/테스트1/public/index.php',
                'indexPage'  => 'index.php',
                'expected'   => 'route',
            ],
            'multibyte_characters_with_nested_subfolder' => [
                'requestUri' => '/%D0%BF%D1%80%D0%BE%D0%B5%D0%BA%D1%82/%D1%82%D0%B5%D1%81%D1%821/index.php/route',
                'scriptName' => '/проект/тест1/public/index.php',
                'indexPage'  => 'index.php',
                'expected'   => 'route',
            ],
        ];
    }
}
