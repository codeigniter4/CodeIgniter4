<?php namespace CodeIgniter\Pager;

use CodeIgniter\HTTP\URI;

class PagerRendererTest extends \CIUnitTestCase
{
	protected $uri;

	//--------------------------------------------------------------------

	public function setUp()
	{
		$this->uri = new URI('http://example.com/foo');
	}

	//--------------------------------------------------------------------

	public function testHasPreviousReturnsFalseWhenFirstIsOne()
	{
	    $details = [
	    	'uri' => $this->uri,
			'pageCount' => 5,
			'currentPage' => 1,
			'total'	=> 100
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
			'uri' => $uri,
			'pageCount' => 10,
			'currentPage' => 5,
			'total'	=> 100
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
			'uri' => $uri,
			'pageCount' => 50,
			'currentPage' => 4,
			'total'	=> 100
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
			'uri' => $uri,
			'pageCount' => 5,
			'currentPage' => 4,
			'total'	=> 100
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
			'uri' => $uri,
			'pageCount' => 50,
			'currentPage' => 4,
			'total'	=> 100
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
			'uri' => $uri,
			'pageCount' => 50,
			'currentPage' => 4,
			'total'	=> 100
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
			'uri' => $this->uri,
			'pageCount' => 50,
			'currentPage' => 4,
			'total'	=> 100
		];

		$pager = new PagerRenderer($details);
		$pager->setSurroundCount(1);

		$expected = [
			[
				'uri' => 'http://example.com/foo?page=3',
				'title' => 3,
				'active' => false
			],
			[
				'uri' => 'http://example.com/foo?page=4',
				'title' => 4,
				'active' => true
			],
			[
				'uri' => 'http://example.com/foo?page=5',
				'title' => 5,
				'active' => false
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
			'uri' => $uri,
			'pageCount' => 50,
			'currentPage' => 4,
			'total'	=> 100
		];

		$pager = new PagerRenderer($details);

		$this->assertEquals('http://example.com/foo?foo=bar&page=1', $pager->getFirst());
		$this->assertEquals('http://example.com/foo?foo=bar&page=50', $pager->getLast());
	}

	//--------------------------------------------------------------------
}
