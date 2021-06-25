<?php

namespace CodeIgniter\Pager;

use CodeIgniter\HTTP\URI;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PagerRendererTest extends CIUnitTestCase
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
        $this->assertSame('http://example.com/foo?foo=bar&page=2', $pager->getPrevious());
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
        $this->assertSame('http://example.com/foo?foo=bar&page=3', $pager->getPrevious());
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
        $this->assertSame('http://example.com/foo?foo=bar&page=7', $pager->getNext());
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
        $this->assertSame('http://example.com/foo?foo=bar&page=5', $pager->getNext());
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

        $this->assertSame($expected, $pager->links());
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

        $this->assertSame('http://example.com/foo?foo=bar&page=1', $pager->getFirst());
        $this->assertSame('http://example.com/foo?foo=bar&page=50', $pager->getLast());
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

        $this->assertSame('http://example.com/foo?foo=bar&page=10', $pager->getCurrent());
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

        $this->assertSame('http://example.com/foo/10?foo=bar', $pager->getCurrent());
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
        $this->assertNull($pager->getPrevious());
        $this->assertNull($pager->getNext());

        // with surropund count of 2
        $pager->setSurroundCount(2);
        $this->assertSame($this->expect . '1', $pager->getPrevious());
        $this->assertSame($this->expect . '7', $pager->getNext());

        // with unchanged surround count
        $pager->setSurroundCount();
        $this->assertSame($this->expect . '1', $pager->getPrevious());
        $this->assertSame($this->expect . '7', $pager->getNext());

        // and with huge surround count
        $pager->setSurroundCount(100);
        $this->assertNull($pager->getPrevious());
        $this->assertNull($pager->getNext());
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
        $this->assertSame('http://example.com/foo/2?foo=bar', $pager->getPrevious());
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
        $this->assertSame('http://example.com/foo/3?foo=bar', $pager->getPrevious());
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
        $this->assertSame('http://example.com/foo/7?foo=bar', $pager->getNext());
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
        $this->assertSame('http://example.com/foo/5?foo=bar', $pager->getNext());
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

        $this->assertSame($expected, $pager->links());
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

        $this->assertSame('http://example.com/foo/1?foo=bar', $pager->getFirst());
        $this->assertSame('http://example.com/foo/50?foo=bar', $pager->getLast());
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
        $this->assertSame('http://example.com/foo?page=2', $pager->getPreviousPage());
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
        $this->assertSame('http://example.com/foo/2', $pager->getPreviousPage());
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
        $this->assertSame('http://example.com/foo?page=4', $pager->getNextPage());
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
        $this->assertSame('http://example.com/foo/4', $pager->getNextPage());
    }

    public function testGetPageNumber()
    {
        $details = [
            'uri'         => $this->uri,
            'pageCount'   => 10,
            'currentPage' => 3,
            'total'       => 100,
            'segment'     => 2,
        ];
        $pager = new PagerRenderer($details);

        $this->assertSame(1, $pager->getFirstPageNumber());
        $this->assertSame(3, $pager->getCurrentPageNumber());
        $this->assertSame(10, $pager->getLastPageNumber());
        $this->assertSame(10, $pager->getPageCount());
    }

    public function testGetPageNumberSetSurroundCount()
    {
        $details = [
            'uri'         => $this->uri,
            'pageCount'   => 10,
            'currentPage' => 5,
            'total'       => 100,
            'segment'     => 2,
        ];
        $pager = new PagerRenderer($details);
        $pager->setSurroundCount(2);

        $this->assertSame(3, $pager->getFirstPageNumber());
        $this->assertSame(5, $pager->getCurrentPageNumber());
        $this->assertSame(7, $pager->getLastPageNumber());
    }

    public function testGetPreviousPageNumber()
    {
        $details = [
            'uri'         => $this->uri,
            'pageCount'   => 10,
            'currentPage' => 5,
            'total'       => 100,
            'segment'     => 2,
        ];
        $pager = new PagerRenderer($details);
        $pager->setSurroundCount(2);

        $this->assertSame(4, $pager->getPreviousPageNumber());
    }

    public function testGetPreviousPageNumberNull()
    {
        $details = [
            'uri'         => $this->uri,
            'pageCount'   => 10,
            'currentPage' => 1,
            'total'       => 100,
            'segment'     => 2,
        ];
        $pager = new PagerRenderer($details);
        $pager->setSurroundCount(2);

        $this->assertNull($pager->getPreviousPageNumber());
    }

    public function testGetNextPageNumber()
    {
        $details = [
            'uri'         => $this->uri,
            'pageCount'   => 10,
            'currentPage' => 5,
            'total'       => 100,
            'segment'     => 2,
        ];
        $pager = new PagerRenderer($details);
        $pager->setSurroundCount(2);

        $this->assertSame(6, $pager->getNextPageNumber());
    }

    public function testGetNextPageNumberNull()
    {
        $details = [
            'uri'         => $this->uri,
            'pageCount'   => 10,
            'currentPage' => 10,
            'total'       => 100,
            'segment'     => 2,
        ];
        $pager = new PagerRenderer($details);
        $pager->setSurroundCount(2);

        $this->assertNull($pager->getNextPageNumber());
    }
}
