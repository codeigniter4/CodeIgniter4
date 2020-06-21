<?php

namespace CodeIgniter\Pager;

use CodeIgniter\HTTP\URI;

class PagerRendererTest extends \CodeIgniter\Test\CIUnitTestCase
{
	/**
	 * @var URI
	 */
	protected $uri;

	//--------------------------------------------------------------------

	protected function setUp(): void
	{
		parent::setUp();

		$this->uri    = new URI('http://example.com/foo');
		$this->expect = 'http://example.com/foo?page=';
	}

	//--------------------------------------------------------------------

	public function testHasPreviousReturnsFalseWhenFirstIsOne()
	{
		$details = [
			'uri'         => $this->uri,
			'pageCount'   => 5,
			'currentPage' => 1,
			'total'       => 100,
		];

		$pager = new PagerRenderer($details);

		$this->assertFalse($pager->hasPrevious());
	}

	//--------------------------------------------------------------------

	public function testHasPreviousReturnsTrueWhenFirstIsMoreThanOne()
	{
		$uri = $this->uri;
		$uri->addQuery('foo', 'bar');

		$details = [
			'uri'         => $uri,
			'pageCount'   => 10,
			'currentPage' => 5,
			'total'       => 100,
		];

		$pager = new PagerRenderer($details);
		$pager->setSurroundCount(2);

		$this->assertTrue($pager->hasPrevious());
		$this->assertEquals('http://example.com/foo?foo=bar&page=2', $pager->getPrevious());
	}

	//--------------------------------------------------------------------

	public function testGetPreviousWhenSurroundCountIsZero()
	{
		$uri = $this->uri;
		$uri->addQuery('foo', 'bar');

		$details = [
			'uri'         => $uri,
			'pageCount'   => 50,
			'currentPage' => 4,
			'total'       => 100,
		];

		$pager = new PagerRenderer($details);
		$pager->setSurroundCount(0);

		$this->assertTrue($pager->hasPrevious());
		$this->assertEquals('http://example.com/foo?foo=bar&page=3', $pager->getPrevious());
	}

	//--------------------------------------------------------------------

	public function testHasNextReturnsFalseWhenLastIsTotal()
	{
		$uri = $this->uri;
		$uri->addQuery('foo', 'bar');

		$details = [
			'uri'         => $uri,
			'pageCount'   => 5,
			'currentPage' => 4,
			'total'       => 100,
		];

		$pager = new PagerRenderer($details);
		$pager->setSurroundCount(2);

		$this->assertFalse($pager->hasNext());
	}

	//--------------------------------------------------------------------

	public function testHasNextReturnsTrueWhenLastIsSmallerThanTotal()
	{
		$uri = $this->uri;
		$uri->addQuery('foo', 'bar');

		$details = [
			'uri'         => $uri,
			'pageCount'   => 50,
			'currentPage' => 4,
			'total'       => 100,
		];

		$pager = new PagerRenderer($details);
		$pager->setSurroundCount(2);

		$this->assertTrue($pager->hasNext());
		$this->assertEquals('http://example.com/foo?foo=bar&page=7', $pager->getNext());
	}

	//--------------------------------------------------------------------

	public function testGetNextWhenSurroundCountIsZero()
	{
		$uri = $this->uri;
		$uri->addQuery('foo', 'bar');

		$details = [
			'uri'         => $uri,
			'pageCount'   => 50,
			'currentPage' => 4,
			'total'       => 100,
		];

		$pager = new PagerRenderer($details);
		$pager->setSurroundCount(0);

		$this->assertTrue($pager->hasNext());
		$this->assertEquals('http://example.com/foo?foo=bar&page=5', $pager->getNext());
	}

	//--------------------------------------------------------------------

	public function testLinksBasics()
	{
		$details = [
			'uri'         => $this->uri,
			'pageCount'   => 50,
			'currentPage' => 4,
			'total'       => 100,
		];

		$pager = new PagerRenderer($details);
		$pager->setSurroundCount(1);

		$expected = [
			[
				'uri'    => 'http://example.com/foo?page=3',
				'title'  => 3,
				'active' => false,
			],
			[
				'uri'    => 'http://example.com/foo?page=4',
				'title'  => 4,
				'active' => true,
			],
			[
				'uri'    => 'http://example.com/foo?page=5',
				'title'  => 5,
				'active' => false,
			],
		];

		$this->assertEquals($expected, $pager->links());
	}

	//--------------------------------------------------------------------

	public function testGetFirstAndGetLast()
	{
		$uri = $this->uri;
		$uri->addQuery('foo', 'bar');

		$details = [
			'uri'         => $uri,
			'pageCount'   => 50,
			'currentPage' => 4,
			'total'       => 100,
		];

		$pager = new PagerRenderer($details);

		$this->assertEquals('http://example.com/foo?foo=bar&page=1', $pager->getFirst());
		$this->assertEquals('http://example.com/foo?foo=bar&page=50', $pager->getLast());
	}

	//--------------------------------------------------------------------

	public function testGetCurrent()
	{
		$uri = $this->uri;
		$uri->addQuery('foo', 'bar');

		$details = [
			'uri'         => $uri,
			'pageCount'   => 50,
			'currentPage' => 10,
			'total'       => 100,
		];

		$pager = new PagerRenderer($details);

		$this->assertEquals('http://example.com/foo?foo=bar&page=10', $pager->getCurrent());
	}

	//--------------------------------------------------------------------

	public function testGetCurrentWithSegment()
	{
		$uri = $this->uri;
		$uri->addQuery('foo', 'bar');

		$details = [
			'uri'         => $uri,
			'pageCount'   => 50,
			'currentPage' => 10,
			'total'       => 100,
			'segment'     => 2,
		];

		$pager = new PagerRenderer($details);

		$this->assertEquals('http://example.com/foo/10?foo=bar', $pager->getCurrent());
	}

	//--------------------------------------------------------------------

	public function testSurroundCount()
	{
		$uri = $this->uri;

		$details = [
			'uri'         => $uri,
			'pageCount'   => 10, // 10 pages
			'currentPage' => 4,
			'total'       => 100, // 100 records, so 10 per page
		];

		$pager = new PagerRenderer($details);

		// without any surround count
		$this->assertEquals(null, $pager->getPrevious());
		$this->assertEquals(null, $pager->getNext());

		// with surropund count of 2
		$pager->setSurroundCount(2);
		$this->assertEquals($this->expect . '1', $pager->getPrevious());
		$this->assertEquals($this->expect . '7', $pager->getNext());

		// with unchanged surround count
		$pager->setSurroundCount();
		$this->assertEquals($this->expect . '1', $pager->getPrevious());
		$this->assertEquals($this->expect . '7', $pager->getNext());

		// and with huge surround count
		$pager->setSurroundCount(100);
		$this->assertEquals(null, $pager->getPrevious());
		$this->assertEquals(null, $pager->getNext());
	}

	//--------------------------------------------------------------------

	public function testHasPreviousReturnsFalseWhenFirstIsOneSegment()
	{
		$details = [
			'uri'         => $this->uri,
			'pageCount'   => 5,
			'currentPage' => 1,
			'total'       => 100,
			'segment'     => 2,
		];

		$pager = new PagerRenderer($details);

		$this->assertFalse($pager->hasPrevious());
	}

	//--------------------------------------------------------------------

	public function testHasPreviousReturnsTrueWhenFirstIsMoreThanOneSegment()
	{
		$uri = $this->uri;
		$uri->addQuery('foo', 'bar');

		$details = [
			'uri'         => $uri,
			'pageCount'   => 10,
			'currentPage' => 5,
			'total'       => 100,
			'segment'     => 2,
		];

		$pager = new PagerRenderer($details);
		$pager->setSurroundCount(2);

		$this->assertTrue($pager->hasPrevious());
		$this->assertEquals('http://example.com/foo/2?foo=bar', $pager->getPrevious());
	}

	//--------------------------------------------------------------------

	public function testGetPreviousWhenSurroundCountIsZeroSegment()
	{
		$uri = $this->uri;
		$uri->addQuery('foo', 'bar');

		$details = [
			'uri'         => $uri,
			'pageCount'   => 50,
			'currentPage' => 4,
			'total'       => 100,
			'segment'     => 2,
		];

		$pager = new PagerRenderer($details);
		$pager->setSurroundCount(0);

		$this->assertTrue($pager->hasPrevious());
		$this->assertEquals('http://example.com/foo/3?foo=bar', $pager->getPrevious());
	}

	//--------------------------------------------------------------------

	public function testHasNextReturnsFalseWhenLastIsTotalSegment()
	{
		$uri = $this->uri;
		$uri->addQuery('foo', 'bar');

		$details = [
			'uri'         => $uri,
			'pageCount'   => 5,
			'currentPage' => 4,
			'total'       => 100,
			'segment'     => 2,
		];

		$pager = new PagerRenderer($details);
		$pager->setSurroundCount(2);

		$this->assertFalse($pager->hasNext());
	}

	//--------------------------------------------------------------------

	public function testHasNextReturnsTrueWhenLastIsSmallerThanTotalSegment()
	{
		$uri = $this->uri;
		$uri->addQuery('foo', 'bar');

		$details = [
			'uri'         => $uri,
			'pageCount'   => 50,
			'currentPage' => 4,
			'total'       => 100,
			'segment'     => 2,
		];

		$pager = new PagerRenderer($details);
		$pager->setSurroundCount(2);

		$this->assertTrue($pager->hasNext());
		$this->assertEquals('http://example.com/foo/7?foo=bar', $pager->getNext());
	}

	//--------------------------------------------------------------------

	public function testGetNextWhenSurroundCountIsZeroSegment()
	{
		$uri = $this->uri;
		$uri->addQuery('foo', 'bar');

		$details = [
			'uri'         => $uri,
			'pageCount'   => 50,
			'currentPage' => 4,
			'total'       => 100,
			'segment'     => 2,
		];

		$pager = new PagerRenderer($details);
		$pager->setSurroundCount(0);

		$this->assertTrue($pager->hasNext());
		$this->assertEquals('http://example.com/foo/5?foo=bar', $pager->getNext());
	}

	//--------------------------------------------------------------------

	public function testLinksBasicsSegment()
	{
		$details = [
			'uri'         => $this->uri,
			'pageCount'   => 50,
			'currentPage' => 4,
			'total'       => 100,
			'segment'     => 2,
		];

		$pager = new PagerRenderer($details);
		$pager->setSurroundCount(1);

		$expected = [
			[
				'uri'    => 'http://example.com/foo/3',
				'title'  => 3,
				'active' => false,
			],
			[
				'uri'    => 'http://example.com/foo/4',
				'title'  => 4,
				'active' => true,
			],
			[
				'uri'    => 'http://example.com/foo/5',
				'title'  => 5,
				'active' => false,
			],
		];

		$this->assertEquals($expected, $pager->links());
	}

	//--------------------------------------------------------------------

	public function testGetFirstAndGetLastSegment()
	{
		$uri = $this->uri;
		$uri->addQuery('foo', 'bar');

		$details = [
			'uri'         => $uri,
			'pageCount'   => 50,
			'currentPage' => 4,
			'total'       => 100,
			'segment'     => 2,
		];

		$pager = new PagerRenderer($details);

		$this->assertEquals('http://example.com/foo/1?foo=bar', $pager->getFirst());
		$this->assertEquals('http://example.com/foo/50?foo=bar', $pager->getLast());
	}

	//--------------------------------------------------------------------

	public function testHasPreviousPageReturnsFalseWhenCurrentPageIsFirst()
	{
		$details = [
			'uri'         => $this->uri,
			'pageCount'   => 5,
			'currentPage' => 1,
			'total'       => 100,
		];

		$pager = new PagerRenderer($details);

		$this->assertNull($pager->getPreviousPage());
		$this->assertFalse($pager->hasPreviousPage());
	}

	//--------------------------------------------------------------------

	public function testHasNextPageReturnsFalseWhenCurrentPageIsLast()
	{
		$details = [
			'uri'         => $this->uri,
			'pageCount'   => 5,
			'currentPage' => 5,
			'total'       => 100,
		];

		$pager = new PagerRenderer($details);

		$this->assertNull($pager->getNextPage());
		$this->assertFalse($pager->hasNextPage());
	}

	//--------------------------------------------------------------------

	public function testHasPreviousPageReturnsTrueWhenFirstIsMoreThanCurrent()
	{
		$uri = $this->uri;

		$details = [
			'uri'         => $uri,
			'pageCount'   => 10,
			'currentPage' => 3,
			'total'       => 100,
		];

		$pager = new PagerRenderer($details);

		$this->assertNotNull($pager->getPreviousPage());
		$this->assertTrue($pager->hasPreviousPage());
		$this->assertEquals('http://example.com/foo?page=2', $pager->getPreviousPage());
	}

	//--------------------------------------------------------------------

	public function testGetPreviousPageWithSegmentHigherThanZero()
	{
		$uri = $this->uri;

		$details = [
			'uri'         => $uri,
			'pageCount'   => 10,
			'currentPage' => 3,
			'total'       => 100,
			'segment'     => 2,
		];

		$pager = new PagerRenderer($details);
		$this->assertEquals('http://example.com/foo/2', $pager->getPreviousPage());
	}

	//--------------------------------------------------------------------

	public function testHasNextPageReturnsTrueWhenLastIsMoreThanCurrent()
	{
		$uri = $this->uri;

		$details = [
			'uri'         => $uri,
			'pageCount'   => 10,
			'currentPage' => 3,
			'total'       => 100,
		];

		$pager = new PagerRenderer($details);

		$this->assertNotNull($pager->getNextPage());
		$this->assertTrue($pager->hasNextPage());
		$this->assertEquals('http://example.com/foo?page=4', $pager->getNextPage());
	}

	public function testGetNextPageWithSegmentHigherThanZero()
	{
		$uri = $this->uri;

		$details = [
			'uri'         => $uri,
			'pageCount'   => 10,
			'currentPage' => 3,
			'total'       => 100,
			'segment'     => 2,
		];

		$pager = new PagerRenderer($details);
		$this->assertEquals('http://example.com/foo/4', $pager->getNextPage());
	}
}
