<?php
namespace CodeIgniter\Helpers;

final class DateHelperTest extends \CodeIgniter\Test\CIUnitTestCase
{

	private $name;
	private $value;
	private $expire;
	private $response;

	protected function setUp(): void
	{
		parent::setUp();
		helper('date');
	}

	//--------------------------------------------------------------------

	public function testNowDefault()
	{
		$time = new \DateTime();
		$this->assertCloseEnough(now(), time());  // close enough
	}

	//--------------------------------------------------------------------

	public function testNowSpecific()
	{
		// Chicago should be two hours ahead of Vancouver
		$this->assertCloseEnough(7200, now('America/Chicago') - now('America/Vancouver'));
	}

}
