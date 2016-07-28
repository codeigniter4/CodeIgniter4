<?php namespace CodeIgniter\Pager;

use Config\Pager;
use Config\Services;

class PagerTest extends \CIUnitTestCase
{
	/**
	 * @var \CodeIgniter\Pager\Pager
	 */
	protected $pager;
	protected $config;

	public function __construct()
	{
		helper('url');
	}

	//--------------------------------------------------------------------

	public function setUp()
	{
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_GET = [];
		$this->config = new Pager();
	    $this->pager = new \CodeIgniter\Pager\Pager($this->config, Services::renderer());
	}

	//--------------------------------------------------------------------

	/**
	 * @group single
	 */
	public function testSetPathRemembersPath()
	{
	    $this->pager->setPath('foo/bar');

		$details = $this->pager->getDetails();

		$this->assertEquals('foo/bar', $details['uri']->getPath());
	}

	//--------------------------------------------------------------------

	public function testGetDetailsRecognizesPageQueryVar()
	{
		$_GET['page'] = 2;

		// Need this to create the group.
		$this->pager->setPath('foo/bar');

	    $details = $this->pager->getDetails();

		$this->assertEquals(2, $details['currentPage']);
	}

	//--------------------------------------------------------------------

	public function testGetDetailsRecognizesGroupedPageQueryVar()
	{
		$_GET['page_foo'] = 2;

		// Need this to create the group.
		$this->pager->setPath('foo/bar', 'foo');

		$details = $this->pager->getDetails('foo');

		$this->assertEquals(2, $details['currentPage']);
	}

	//--------------------------------------------------------------------

	public function testGetDetailsThrowExceptionIfGroupNotFound()
	{
	    $this->setExpectedException('InvalidArgumentException');

		$this->pager->getDetails('foo');
	}

	//--------------------------------------------------------------------

	public function testDetailsHasConfiguredPerPageValue()
	{
		// Need this to create the group.
		$this->pager->setPath('foo/bar', 'foo');

		$details = $this->pager->getDetails('foo');

		$this->assertEquals($this->config->perPage, $details['perPage']);
	}

	//--------------------------------------------------------------------

	public function testStoreDoesBasicCalcs()
	{
		$this->pager->store('foo', 3, 25, 100);

		$details = $this->pager->getDetails('foo');

		$this->assertEquals($details['total'], 100);
		$this->assertEquals($details['perPage'], 25);
		$this->assertEquals($details['currentPage'], 3);
	}

	//--------------------------------------------------------------------

	public function testStoreAndHasMore()
	{
		$this->pager->store('foo', 3, 25, 100);

		$this->assertTrue($this->pager->hasMore('foo'));
	}

	//--------------------------------------------------------------------

	public function testStoreAndHasMoreCanBeFalse()
	{
		$this->pager->store('foo', 3, 25, 70);

		$this->assertFalse($this->pager->hasMore('foo'));
	}

	//--------------------------------------------------------------------

	public function testHasMoreDefaultsToFalse()
	{
		$this->assertFalse($this->pager->hasMore('foo'));
	}

	//--------------------------------------------------------------------

	public function testPerPageHasDefaultValue()
	{
	    $this->assertEquals($this->config->perPage, $this->pager->getPerPage());
	}

	//--------------------------------------------------------------------

	public function testPerPageKeepsStoredValue()
	{
		$this->pager->store('foo', 3, 13, 70);

		$this->assertEquals(13, $this->pager->getPerPage('foo'));
	}

	//--------------------------------------------------------------------

	public function testGetCurrentPageDefaultsToOne()
	{
		$this->assertEquals(1, $this->pager->getCurrentPage());
	}

	//--------------------------------------------------------------------

	public function testGetCurrentPageRemembersStoredPage()
	{
		$this->pager->store('foo', 3, 13, 70);

		$this->assertEquals(3, $this->pager->getCurrentPage('foo'));
	}

	//--------------------------------------------------------------------

	public function testGetCurrentPageDetectsURI()
	{
		$_GET['page'] = 2;

		$this->assertEquals(2, $this->pager->getCurrentPage());
	}

	//--------------------------------------------------------------------

	public function testGetCurrentPageDetectsGroupedURI()
	{
		$_GET['page_foo'] = 2;

		$this->assertEquals(2, $this->pager->getCurrentPage('foo'));
	}

	//--------------------------------------------------------------------

	public function testGetTotalPagesDefaultsToOne()
	{
	    $this->assertEquals(1, $this->pager->getPageCount());
	}

	//--------------------------------------------------------------------

	public function testGetTotalPagesCalcsCorrectValue()
	{
		$this->pager->store('foo', 3, 12, 70);

		$this->assertEquals(6, $this->pager->getPageCount('foo'));
	}

	//--------------------------------------------------------------------

	public function testGetNextURIUsesCurrentURI()
	{
		$_GET['page'] = 2;

		$this->pager->store('foo', 2, 12, 70);

		$expected = current_url(true);
		$expected = (string)$expected->setQuery('page=3');

		$this->assertEquals((string)$expected, $this->pager->getNextPageURI('foo'));
	}

	//--------------------------------------------------------------------

	public function testGetNextURIReturnsNullOnLastPage()
	{
		$this->pager->store('foo', 6, 12, 70);

		$this->assertNull($this->pager->getNextPageURI('foo'));
	}

	//--------------------------------------------------------------------

	public function testGetNextURICorrectOnFirstPage()
	{
		$this->pager->store('foo', 1, 12, 70);

		$expected = current_url(true);
		$expected = (string)$expected->setQuery('page=2');

		$this->assertEquals($expected, $this->pager->getNextPageURI('foo'));
	}

	//--------------------------------------------------------------------

	public function testGetPreviousURIUsesCurrentURI()
	{
		$_GET['page'] = 2;

		$this->pager->store('foo', 2, 12, 70);

		$expected = current_url(true);
		$expected = (string)$expected->setQuery('page=1');

		$this->assertEquals((string)$expected, $this->pager->getPreviousPageURI('foo'));
	}

	//--------------------------------------------------------------------

	public function testGetNextURIReturnsNullOnFirstPage()
	{
		$this->pager->store('foo', 1, 12, 70);

		$this->assertNull($this->pager->getPreviousPageURI('foo'));
	}

	//--------------------------------------------------------------------
}
