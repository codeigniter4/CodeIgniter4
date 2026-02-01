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

namespace CodeIgniter\Pager;

use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Pager\Exceptions\PagerException;
use CodeIgniter\Superglobals;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\Pager as PagerConfig;
use Config\Services;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[BackupGlobals(true)]
#[Group('Others')]
final class PagerTest extends CIUnitTestCase
{
    private ?Pager $pager        = null;
    private ?PagerConfig $config = null;

    protected function setUp(): void
    {
        parent::setUp();

        Services::injectMock('superglobals', new Superglobals([], [], [], [], [], []));

        $this->createPager('/');
    }

    private function createPager(string $requestUri): void
    {
        service('superglobals')
            ->setServer('REQUEST_URI', $requestUri)
            ->setServer('SCRIPT_NAME', '/index.php');

        $config            = new App();
        $config->baseURL   = 'http://example.com/';
        $config->indexPage = '';
        Factories::injectMock('config', 'App', $config);

        $request = new IncomingRequest(
            $config,
            new SiteURI($config, ltrim($requestUri, '/')),
            'php://input',
            new UserAgent(),
        );
        $request = $request->withMethod('GET');
        Services::injectMock('request', $request);

        $this->config = new PagerConfig();
        $this->pager  = new Pager($this->config, service('renderer'));
    }

    public function testSetPathRemembersPath(): void
    {
        $this->pager->setPath('foo/bar');

        $details = $this->pager->getDetails();

        $this->assertSame('foo/bar', $details['uri']->getRoutePath());
    }

    public function testGetDetailsRecognizesPageQueryVar(): void
    {
        service('superglobals')->setGet('page', '2');

        // Need this to create the group.
        $this->pager->setPath('foo/bar');

        $details = $this->pager->getDetails();

        $this->assertSame(2, $details['currentPage']);
    }

    public function testGetDetailsRecognizesGroupedPageQueryVar(): void
    {
        service('superglobals')->setGet('page_foo', '2');

        // Need this to create the group.
        $this->pager->setPath('foo/bar', 'foo');

        $details = $this->pager->getDetails('foo');

        $this->assertSame(2, $details['currentPage']);
    }

    public function testGetDetailsThrowExceptionIfGroupNotFound(): void
    {
        $this->expectException(PagerException::class);

        $this->pager->getDetails('foo');
    }

    public function testDetailsHasConfiguredPerPageValue(): void
    {
        // Need this to create the group.
        $this->pager->setPath('foo/bar', 'foo');

        $details = $this->pager->getDetails('foo');

        $this->assertSame($this->config->perPage, $details['perPage']);
    }

    public function testStoreDoesBasicCalcs(): void
    {
        $this->pager->store('foo', 3, 25, 100);

        $details = $this->pager->getDetails('foo');

        $this->assertSame(100, $details['total']);
        $this->assertSame(25, $details['perPage']);
        $this->assertSame(3, $details['currentPage']);
    }

    public function testStoreDoesBasicCalcsOnPerPageReadFromPagerConfig(): void
    {
        $this->pager->store('foo', 3, null, 100);

        $details = $this->pager->getDetails('foo');

        $this->assertSame(100, $details['total']);
        $this->assertSame(20, $details['perPage']);
        $this->assertSame(3, $details['currentPage']);
    }

    public function testStoreAndHasMore(): void
    {
        $this->pager->store('foo', 3, 25, 100);

        $this->assertTrue($this->pager->hasMore('foo'));
    }

    public function testStoreAndHasMoreCanBeFalse(): void
    {
        $this->pager->store('foo', 3, 25, 70);

        $this->assertFalse($this->pager->hasMore('foo'));
    }

    public function testStoreWithQueries(): void
    {
        service('superglobals')
            ->setGet('page', '3')
            ->setGet('foo', 'bar');

        $this->pager->store('default', 3, 25, 100);

        $this->assertSame('http://example.com/?page=2&foo=bar', $this->pager->getPreviousPageURI());
        $this->assertSame('http://example.com/?page=4&foo=bar', $this->pager->getNextPageURI());
        $this->assertSame('http://example.com/?page=5&foo=bar', $this->pager->getPageURI(5));
        $this->assertSame(
            'http://example.com/?foo=bar&page=5',
            $this->pager->only(['foo'])->getPageURI(5),
        );
        $this->assertSame(
            'http://example.com/?page=5',
            $this->pager->only([])->getPageURI(5),
        );
    }

    public function testStoreWithSegments(): void
    {
        service('superglobals')
            ->setGet('page', '3')
            ->setGet('foo', 'bar');

        $this->pager->store('default', 3, 25, 100, 1);

        $this->assertSame('http://example.com/2?page=3&foo=bar', $this->pager->getPreviousPageURI());
        $this->assertSame('http://example.com/4?page=3&foo=bar', $this->pager->getNextPageURI());
        $this->assertSame('http://example.com/5?page=3&foo=bar', $this->pager->getPageURI(5));
        $this->assertSame(
            'http://example.com/5?foo=bar',
            $this->pager->only(['foo'])->getPageURI(5),
        );
        $this->assertSame(
            'http://example.com/5',
            $this->pager->only([])->getPageURI(5),
        );
    }

    public function testGetPageURIWithURIReturnObject(): void
    {
        $this->pager->store('bar', 5, 25, 100, 1);

        $uri = $this->pager->getPageURI(7, 'bar', true);

        $this->assertInstanceOf(URI::class, $uri);
    }

    public function testHasMoreDefaultsToFalse(): void
    {
        $this->assertFalse($this->pager->hasMore('foo'));
    }

    public function testPerPageHasDefaultValue(): void
    {
        $this->assertSame($this->config->perPage, $this->pager->getPerPage());
    }

    public function testPerPageKeepsStoredValue(): void
    {
        $this->pager->store('foo', 3, 13, 70);

        $this->assertSame(13, $this->pager->getPerPage('foo'));
    }

    public function testGetCurrentPageDefaultsToOne(): void
    {
        $this->assertSame(1, $this->pager->getCurrentPage());
    }

    public function testGetCurrentPageRemembersStoredPage(): void
    {
        $this->pager->store('foo', 3, 13, 70);

        $this->assertSame(3, $this->pager->getCurrentPage('foo'));
    }

    public function testGetCurrentPageDetectsURI(): void
    {
        service('superglobals')->setGet('page', '2');

        $this->assertSame(2, $this->pager->getCurrentPage());
    }

    public function testGetCurrentPageDetectsGroupedURI(): void
    {
        service('superglobals')->setGet('page_foo', '2');

        $this->assertSame(2, $this->pager->getCurrentPage('foo'));
    }

    public function testGetCurrentPageFromSegment(): void
    {
        $this->createPager('/page/2');

        $this->pager->setPath('foo');
        $this->pager->setSegment(2);

        $this->assertSame(2, $this->pager->getCurrentPage());
    }

    public function testGetTotalPagesDefaultsToOne(): void
    {
        $this->assertSame(1, $this->pager->getPageCount());
    }

    public function testGetTotalCorrectValue(): void
    {
        $this->pager->store('foo', 3, 12, 70);

        $this->assertSame(70, $this->pager->getTotal('foo'));
    }

    public function testGetTotalPagesCalcsCorrectValue(): void
    {
        $this->pager->store('foo', 3, 12, 70);

        $this->assertSame(6, $this->pager->getPageCount('foo'));
    }

    public function testGetNextURIUsesCurrentURI(): void
    {
        service('superglobals')->setGet('page_foo', '2');

        $this->pager->store('foo', 2, 12, 70);

        $expected = current_url(true);
        $expected = (string) $expected->setQuery('page_foo=3');

        $this->assertSame($expected, $this->pager->getNextPageURI('foo'));
    }

    public function testGetNextURIReturnsNullOnLastPage(): void
    {
        $this->pager->store('foo', 6, 12, 70);

        $this->assertNull($this->pager->getNextPageURI('foo'));
    }

    public function testGetNextURICorrectOnFirstPage(): void
    {
        $this->pager->store('foo', 1, 12, 70);

        $expected = current_url(true);
        $expected = (string) $expected->setQuery('page_foo=2');

        $this->assertSame($expected, $this->pager->getNextPageURI('foo'));
    }

    public function testGetPreviousURIUsesCurrentURI(): void
    {
        service('superglobals')->setGet('page_foo', '2');

        $this->pager->store('foo', 2, 12, 70);

        $expected = current_url(true);
        $expected = (string) $expected->setQuery('page_foo=1');

        $this->assertSame($expected, $this->pager->getPreviousPageURI('foo'));
    }

    public function testGetNextURIReturnsNullOnFirstPage(): void
    {
        $this->pager->store('foo', 1, 12, 70);

        $this->assertNull($this->pager->getPreviousPageURI('foo'));
    }

    public function testGetNextURIWithQueryStringUsesCurrentURI(): void
    {
        service('superglobals')
            ->setGet('page_foo', '3')
            ->setGet('status', '1');

        $getArray = service('superglobals')->getGetArray();
        $expected = current_url(true);
        $expected = (string) $expected->setQueryArray($getArray);

        $this->pager->store('foo', (int) $getArray['page_foo'] - 1, 12, 70);

        $this->assertSame($expected, $this->pager->getNextPageURI('foo'));
    }

    public function testGetPreviousURIWithQueryStringUsesCurrentURI(): void
    {
        service('superglobals')
            ->setGet('page_foo', '1')
            ->setGet('status', '1');

        $getArray = service('superglobals')->getGetArray();
        $expected = current_url(true);
        $expected = (string) $expected->setQueryArray($getArray);

        $this->pager->store('foo', (int) $getArray['page_foo'] + 1, 12, 70);

        $this->assertSame($expected, $this->pager->getPreviousPageURI('foo'));
    }

    public function testGetOnlyQueries(): void
    {
        $getArray = [
            'page'     => '2',
            'search'   => 'foo',
            'order'    => 'asc',
            'hello'    => 'xxx',
            'category' => 'baz',
        ];
        service('superglobals')->setGetArray($getArray);

        $onlyQueries = [
            'search',
            'order',
        ];

        $this->pager->store('default', (int) $getArray['page'], 10, 100);

        $uri = current_url(true);

        $this->assertSame(
            $this->pager->only($onlyQueries)->getPreviousPageURI(),
            (string) $uri->setQuery('search=foo&order=asc&page=1'),
        );
        $this->assertSame(
            $this->pager->only($onlyQueries)->getNextPageURI(),
            (string) $uri->setQuery('search=foo&order=asc&page=3'),
        );
        $this->assertSame(
            $this->pager->only($onlyQueries)->getPageURI(4),
            (string) $uri->setQuery('search=foo&order=asc&page=4'),
        );
    }

    public function testBadTemplate(): void
    {
        $this->expectException(PagerException::class);
        $this->pager->links('default', 'bogus');
    }

    // the tests below are looking for specific <ul> elements.
    // not the most rigorous, but a start :-/

    public function testLinks(): void
    {
        $this->assertStringContainsString('<ul class="pagination">', $this->pager->links());
    }

    public function testSimpleLinks(): void
    {
        $this->assertStringContainsString('<ul class="pager">', $this->pager->simpleLinks());
    }

    public function testMakeLinks(): void
    {
        $this->assertStringContainsString(
            '<ul class="pagination">',
            $this->pager->makeLinks(4, 10, 50),
        );
        $this->assertStringContainsString(
            '<ul class="pagination">',
            $this->pager->makeLinks(4, 10, 50, 'default_full'),
        );
        $this->assertStringContainsString(
            '<ul class="pager">',
            $this->pager->makeLinks(4, 10, 50, 'default_simple'),
        );
        $this->assertStringContainsString(
            '<link rel="canonical"',
            $this->pager->makeLinks(4, 10, 50, 'default_head'),
        );
        $this->assertStringContainsString(
            '?page=1',
            $this->pager->makeLinks(1, 10, 1, 'default_full', 0),
        );
        $this->assertStringContainsString(
            '?page=1',
            $this->pager->makeLinks(1, 10, 1, 'default_full', 0, ''),
        );
        $this->assertStringContainsString(
            '?page=1',
            $this->pager->makeLinks(1, 10, 1, 'default_full', 0, 'default'),
        );
        $this->assertStringContainsString(
            '?page_custom=1',
            $this->pager->makeLinks(1, 10, 1, 'default_full', 0, 'custom'),
        );
        $this->assertStringContainsString(
            '?page_custom=1',
            $this->pager->makeLinks(1, null, 1, 'default_full', 0, 'custom'),
        );
        $this->assertStringContainsString(
            '/1',
            $this->pager->makeLinks(1, 10, 1, 'default_full', 1),
        );
        $this->assertStringContainsString(
            '<li class="active">',
            $this->pager->makeLinks(1, 10, 1, 'default_full', 1),
        );
    }

    public function testHeadLinks(): void
    {
        $firstPage = $this->pager->makeLinks(1, 10, 50, 'default_head');

        $this->assertStringNotContainsString('<link rel="prev"', $firstPage);
        $this->assertStringContainsString('<link rel="canonical"', $firstPage);
        $this->assertStringContainsString('<link rel="next"', $firstPage);

        $secondPage = $this->pager->makeLinks(2, 10, 50, 'default_head');

        $this->assertStringContainsString('<link rel="prev"', $secondPage);
        $this->assertStringContainsString('<link rel="canonical"', $secondPage);
        $this->assertStringContainsString('<link rel="next"', $secondPage);

        $lastPage = $this->pager->makeLinks(5, 10, 50, 'default_head');

        $this->assertStringContainsString('<link rel="prev"', $lastPage);
        $this->assertStringContainsString('<link rel="canonical"', $lastPage);
        $this->assertStringNotContainsString('<link rel="next"', $lastPage);
    }

    public function testBasedURI(): void
    {
        service('superglobals')
            ->setServer('HTTP_HOST', 'example.com')
            ->setServer('REQUEST_URI', '/ci/v4/x/y')
            ->setServer('SCRIPT_NAME', '/ci/v4/index.php');

        $config            = new App();
        $config->baseURL   = 'http://example.com/ci/v4/';
        $config->indexPage = 'fc.php';
        Factories::injectMock('config', 'App', $config);

        $request = new IncomingRequest(
            $config,
            new SiteURI($config, 'x/y'),
            'php://input',
            new UserAgent(),
        );
        $request = $request->withMethod('GET');
        Services::injectMock('request', $request);

        $this->config = new PagerConfig();
        $this->pager  = new Pager($this->config, service('renderer'));

        service('superglobals')->setGet('page_foo', '2');

        $this->pager->store('foo', 2, 12, 70);

        $expected = current_url(true);
        $expected = (string) $expected->setQuery('page_foo=1');

        $this->assertSame($expected, $this->pager->getPreviousPageURI('foo'));
    }

    public function testAccessPageMoreThanPageCountGetLastPage(): void
    {
        $this->pager->store('default', 11, 1, 10);
        $this->assertSame(10, $this->pager->getCurrentPage());
    }

    public function testSegmentOutOfBound(): void
    {
        $this->pager->store('default', 10, 1, 10, 1000);
        $this->assertSame(1, $this->pager->getCurrentPage());
    }
}
