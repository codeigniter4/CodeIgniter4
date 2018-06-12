<?php namespace CodeIgniter\HTTP;

use Config\App;
use CodeIgniter\Config\Services;
use Tests\Support\HTTP\MockResponse;

final class DateHelperTest extends \CIUnitTestCase
{

	private $name;
	private $value;
	private $expire;
	private $response;

	public function setUp()
	{
		parent::setUp();
		helper('date');
	}

	//--------------------------------------------------------------------

	public function testNowDefault()
	{
		$time = new \DateTime();
		$this->assertLessThan(1, abs(now() - time()));  // close enough
	}

	//--------------------------------------------------------------------

	public function testNowSpecific()
	{
		// Chicago should be two hours ahead of Vancouver
		$this->assertEquals(7200,now('America/Chicago')-now('America/Vancouver'));
	}

}
