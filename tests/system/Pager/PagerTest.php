<?php namespace CodeIgniter\Pager;

use CodeIgniter\HTTP\URI;
use CodeIgniter\Pager\Exceptions\PagerException;
use Config\App;
use Config\Pager;
use Config\Services;

/**
 * @backupGlobals enabled
 */
class PagerTest extends \CodeIgniter\Test\CIUnitTestCase
{

	/**
	 * @var \CodeIgniter\Pager\Pager
	 */
	protected $pager;
	protected $config;

	protected function setUp(): void
	{
		parent::setUp();
		helper('url');

		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/';
		$_GET                   = [];

		$config          = new App();
		$config->baseURL = 'http://example.com/';
		$request         = Services::request($config);
		$request->uri    = new URI('http://example.com');

		Services::injectMock('request', $request);

		$_GET         = [];
		$this->config = new Pager();
		$this->pager  = new \CodeIgniter\Pager\Pager($this->config, Services::renderer());
	}

	public function testSetPathRemembersPath()
	{
		$this->pager->setPath('foo/bar');

		$details = $this->pager->getDetails();

		$this->assertEquals('foo/bar', $details['uri']->getPath());
	}

	public function testGetDetailsRecognizesPageQueryVar()
	{
		$_GET['page'] = 2;

		// Need this to create the group.
		$this->pager->setPath('foo/bar');

		$details = $this->pager->getDetails();

		$this->assertEquals(2, $details['currentPage']);
	}

	public function testGetDetailsRecognizesGroupedPageQueryVar()
	{
		$_GET['page_foo'] = 2;

		// Need this to create the group.
		$this->pager->setPath('foo/bar', 'foo');

		$details = $this->pager->getDetails('foo');

		$this->assertEquals(2, $details['currentPage']);
	}

	public function testGetDetailsThrowExceptionIfGroupNotFound()
	{
		$this->expectException(PagerException::class);

		$this->pager->getDetails('foo');
	}

	public function testDetailsHasConfiguredPerPageValue()
	{
		// Need this to create the group.
		$this->pager->setPath('foo/bar', 'foo');

		$details = $this->pager->getDetails('foo');

		$this->assertEquals($this->config->perPage, $details['perPage']);
	}

	public function testStoreDoesBasicCalcs()
	{
		$this->pager->store('foo', 3, 25, 100);

		$details = $this->pager->getDetails('foo');

		$this->assertEquals($details['total'], 100);
		$this->assertEquals($details['perPage'], 25);
		$this->assertEquals($details['currentPage'], 3);
	}

	public function testStoreDoesBasicCalcsOnPerPageReadFromPagerConfig()
	{
		$this->pager->store('foo', 3, null, 100);

		$details = $this->pager->getDetails('foo');

		$this->assertEquals($details['total'], 100);
		$this->assertEquals($details['perPage'], 20);
		$this->assertEquals($details['currentPage'], 3);
	}

	public function testStoreAndHasMore()
	{
		$this->pager->store('foo', 3, 25, 100);

		$this->assertTrue($this->pager->hasMore('foo'));
	}

	public function testStoreAndHasMoreCanBeFalse()
	{
		$this->pager->store('foo', 3, 25, 70);

		$this->assertFalse($this->pager->hasMore('foo'));
	}

	public function testStoreWithQueries()
	{
		$_GET['page'] = 3;
		$_GET['foo']  = 'bar';

		$this->pager->store('default', 3, 25, 100);

		$this->assertEquals('http://example.com?page=2&foo=bar', $this->pager->getPreviousPageURI());
		$this->assertEquals('http://example.com?page=4&foo=bar', $this->pager->getNextPageURI());
		$this->assertEquals('http://example.com?page=5&foo=bar', $this->pager->getPageURI(5));
		$this->assertEquals(
			'http://example.com?foo=bar&page=5',
			$this->pager->only(['foo'])->getPageURI(5)
		);
	}

	public function testStoreWithSegments()
	{
		$_GET['page'] = 3;
		$_GET['foo']  = 'bar';

		$this->pager->store('default', 3, 25, 100, 1);

		$this->assertEquals('http://example.com/2?page=3&foo=bar', $this->pager->getPreviousPageURI());
		$this->assertEquals('http://example.com/4?page=3&foo=bar', $this->pager->getNextPageURI());
		$this->assertEquals('http://example.com/5?page=3&foo=bar', $this->pager->getPageURI(5));
		$this->assertEquals(
			'http://example.com/5?foo=bar',
			$this->pager->only(['foo'])->getPageURI(5)
		);
	}

	public function testHasMoreDefaultsToFalse()
	{
		$this->assertFalse($this->pager->hasMore('foo'));
	}

	public function testPerPageHasDefaultValue()
	{
		$this->assertEquals($this->config->perPage, $this->pager->getPerPage());
	}

	public function testPerPageKeepsStoredValue()
	{
		$this->pager->store('foo', 3, 13, 70);

		$this->assertEquals(13, $this->pager->getPerPage('foo'));
	}

	public function testGetCurrentPageDefaultsToOne()
	{
		$this->assertEquals(1, $this->pager->getCurrentPage());
	}

	public function testGetCurrentPageRemembersStoredPage()
	{
		$this->pager->store('foo', 3, 13, 70);

		$this->assertEquals(3, $this->pager->getCurrentPage('foo'));
	}

	public function testGetCurrentPageDetectsURI()
	{
		$_GET['page'] = 2;

		$this->assertEquals(2, $this->pager->getCurrentPage());
	}

	public function testGetCurrentPageDetectsGroupedURI()
	{
		$_GET['page_foo'] = 2;

		$this->assertEquals(2, $this->pager->getCurrentPage('foo'));
	}

	public function testGetTotalPagesDefaultsToOne()
	{
		$this->assertEquals(1, $this->pager->getPageCount());
	}

	public function testGetTotalCorrectValue()
	{
		$this->pager->store('foo', 3, 12, 70);

		$this->assertEquals(70, $this->pager->getTotal('foo'));
	}

	public function testGetTotalPagesCalcsCorrectValue()
	{
		$this->pager->store('foo', 3, 12, 70);

		$this->assertEquals(6, $this->pager->getPageCount('foo'));
	}

	public function testGetNextURIUsesCurrentURI()
	{
		$_GET['page_foo'] = 2;

		$this->pager->store('foo', 2, 12, 70);

		$expected = current_url(true);
		$expected = (string)$expected->setQuery('page_foo=3');

		$this->assertEquals((string)$expected, $this->pager->getNextPageURI('foo'));
	}

	public function testGetNextURIReturnsNullOnLastPage()
	{
		$this->pager->store('foo', 6, 12, 70);

		$this->assertNull($this->pager->getNextPageURI('foo'));
	}

	public function testGetNextURICorrectOnFirstPage()
	{
		$this->pager->store('foo', 1, 12, 70);

		$expected = current_url(true);
		$expected = (string)$expected->setQuery('page_foo=2');

		$this->assertEquals($expected, $this->pager->getNextPageURI('foo'));
	}

	public function testGetPreviousURIUsesCurrentURI()
	{
		$_GET['page_foo'] = 2;

		$this->pager->store('foo', 2, 12, 70);

		$expected = current_url(true);
		$expected = (string)$expected->setQuery('page_foo=1');

		$this->assertEquals((string)$expected, $this->pager->getPreviousPageURI('foo'));
	}

	public function testGetNextURIReturnsNullOnFirstPage()
	{
		$this->pager->store('foo', 1, 12, 70);

		$this->assertNull($this->pager->getPreviousPageURI('foo'));
	}

	public function testGetNextURIWithQueryStringUsesCurrentURI()
	{
		$_GET = [
			'page_foo' => 3,
			'status'   => 1,
		];

		$expected = current_url(true);
		$expected = (string)$expected->setQueryArray($_GET);

		$this->pager->store('foo', $_GET['page_foo'] - 1, 12, 70);

		$this->assertEquals((string)$expected, $this->pager->getNextPageURI('foo'));
	}

	public function testGetPreviousURIWithQueryStringUsesCurrentURI()
	{
		$_GET     = [
			'page_foo' => 1,
			'status'   => 1,
		];
		$expected = current_url(true);
		$expected = (string)$expected->setQueryArray($_GET);

		$this->pager->store('foo', $_GET['page_foo'] + 1, 12, 70);

		$this->assertEquals((string)$expected, $this->pager->getPreviousPageURI('foo'));
	}

	public function testGetOnlyQueries()
	{
		$_GET        = [
			'page'     => 2,
			'search'   => 'foo',
			'order'    => 'asc',
			'hello'    => 'xxx',
			'category' => 'baz',
		];
		$onlyQueries = [
			'search',
			'order',
		];

		$this->pager->store('default', $_GET['page'], 10, 100);

		$uri = current_url(true);

		$this->assertEquals(
			$this->pager->only($onlyQueries)
						->getPreviousPageURI(), (string)$uri->setQuery('search=foo&order=asc&page=1')
		);
		$this->assertEquals(
			$this->pager->only($onlyQueries)
						->getNextPageURI(), (string)$uri->setQuery('search=foo&order=asc&page=3')
		);
		$this->assertEquals(
			$this->pager->only($onlyQueries)
						->getPageURI(4), (string)$uri->setQuery('search=foo&order=asc&page=4')
		);
	}

	public function testBadTemplate()
	{
		$this->expectException(PagerException::class);
		$this->pager->links('default', 'bogus');
	}

	// the tests below are looking for specific <ul> elements.
	// not the most rigorous, but a start :-/

	public function testLinks()
	{
		$this->assertStringContainsString('<ul class="pagination">', $this->pager->links());
	}

	public function testSimpleLinks()
	{
		$this->assertStringContainsString('<ul class="pager">', $this->pager->simpleLinks());
	}

	public function testMakeLinks()
	{
		$this->assertStringContainsString(
			'<ul class="pagination">', $this->pager->makeLinks(4, 10, 50)
		);
		$this->assertStringContainsString(
			'<ul class="pagination">', $this->pager->makeLinks(4, 10, 50, 'default_full')
		);
		$this->assertStringContainsString(
			'<ul class="pager">', $this->pager->makeLinks(4, 10, 50, 'default_simple')
		);
		$this->assertStringContainsString(
			'<link rel="canonical"', $this->pager->makeLinks(4, 10, 50, 'default_head')
		);
		$this->assertStringContainsString(
			'?page=1', $this->pager->makeLinks(1, 10, 1, 'default_full', 0)
		);
		$this->assertStringContainsString(
			'?page=1', $this->pager->makeLinks(1, 10, 1, 'default_full', 0, '')
		);
		$this->assertStringContainsString(
			'?page=1', $this->pager->makeLinks(1, 10, 1, 'default_full', 0, 'default')
		);
		$this->assertStringContainsString(
			'?page_custom=1', $this->pager->makeLinks(1, 10, 1, 'default_full', 0, 'custom')
		);
		$this->assertStringContainsString(
			'?page_custom=1', $this->pager->makeLinks(1, null, 1, 'default_full', 0, 'custom')
		);
		$this->assertStringContainsString(
			'/1', $this->pager->makeLinks(1, 10, 1, 'default_full', 1)
		);
		$this->assertStringContainsString(
			'<li class="active">', $this->pager->makeLinks(1, 10, 1, 'default_full', 1)
		);
	}

	public function testHeadLinks()
	{
		$first_page = $this->pager->makeLinks(1, 10, 50, 'default_head');

		$this->assertStringNotContainsString('<link rel="prev"', $first_page);
		$this->assertStringContainsString('<link rel="canonical"', $first_page);
		$this->assertStringContainsString('<link rel="next"', $first_page);

		$second_page = $this->pager->makeLinks(2, 10, 50, 'default_head');

		$this->assertStringContainsString('<link rel="prev"', $second_page);
		$this->assertStringContainsString('<link rel="canonical"', $second_page);
		$this->assertStringContainsString('<link rel="next"', $second_page);

		$last_page = $this->pager->makeLinks(5, 10, 50, 'default_head');

		$this->assertStringContainsString('<link rel="prev"', $last_page);
		$this->assertStringContainsString('<link rel="canonical"', $last_page);
		$this->assertStringNotContainsString('<link rel="next"', $last_page);
	}

	public function testBasedURI()
	{
		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['REQUEST_URI'] = '/ci/v4/x/y';
		$_GET                   = [];

		$config            = new App();
		$config->baseURL   = 'http://example.com/ci/v4/';
		$config->indexPage = 'fc.php';
		$request           = Services::request($config);
		$request->uri      = new URI('http://example.com/ci/v4/x/y');

		Services::injectMock('request', $request);

		$this->config = new Pager();
		$this->pager  = new \CodeIgniter\Pager\Pager($this->config, Services::renderer());

		$_GET['page_foo'] = 2;

		$this->pager->store('foo', 2, 12, 70);

		$expected = current_url(true);
		$expected = (string)$expected->setQuery('page_foo=1');

		$this->assertEquals((string)$expected, $this->pager->getPreviousPageURI('foo'));
	}

	public function testAccessPageMoreThanPageCountGetLastPage()
	{
		$this->pager->store('default', 11, 1, 10);
		$this->assertEquals(10, $this->pager->getCurrentPage());
	}

	public function testSegmentOutOfBound()
	{
		$this->pager->store('default', 10, 1, 10, 1000);
		$this->assertEquals(1, $this->pager->getCurrentPage());
	}

}
