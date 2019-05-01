<?php
namespace CodeIgniter\Test;

use CodeIgniter\Events\Events;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use CodeIgniter\HTTP\Response;
use Config\App;

class TestCaseTest extends \CIUnitTestCase
{

	//  protected function tearDown()
	//  {
	//      $buffer = ob_clean();
	//      if (ob_get_level() > 0)
	//      {
	//          ob_end_clean();
	//      }
	//  }
	//
	public function testGetPrivatePropertyWithObject()
	{
		$obj    = new __TestForReflectionHelper();
		$actual = $this->getPrivateProperty($obj, 'private');
		$this->assertEquals('secret', $actual);
	}

	//--------------------------------------------------------------------

	public function testLogging()
	{
		log_message('error', 'Some variable did not contain a value.');
		$this->assertLogged('error', 'Some variable did not contain a value.');
	}

	//--------------------------------------------------------------------

	public function testEventTriggering()
	{
		Events::on('foo', function ($arg) use (&$result) {
			$result = $arg;
		});

		Events::trigger('foo', 'bar');

		$this->assertEventTriggered('foo');
	}

	//--------------------------------------------------------------------

	public function testStreamFilter()
	{
		CITestStreamFilter::$buffer = '';
		$this->stream_filter        = stream_filter_append(STDOUT, 'CITestStreamFilter');
		\CodeIgniter\CLI\CLI::write('first.');
		$expected = "first.\n";
		$this->assertEquals($expected, CITestStreamFilter::$buffer);
		stream_filter_remove($this->stream_filter);
	}

	//--------------------------------------------------------------------
	/**
	 * PHPunit emits headers before we get nominal control of
	 * the output stream, making header testing awkward, to say
	 * the least. This test is intended to make sure that this
	 * is happening as expected.
	 *
	 * TestCaseEmissionsTest is intended to circumvent PHPunit,
	 * and allow us to test our own header emissions.
	 */
	public function testPHPUnitHeadersEmitted()
	{
		$response = new Response(new App());
		$response->pretend(true);

		$body = 'Hello';
		$response->setBody($body);

		ob_start();
		$response->send();
		ob_end_clean();

		// Did PHPunit do its thing?
		$this->assertHeaderEmitted('Content-type: text/html;');
		$this->assertHeaderNotEmitted('Set-Cookie: foo=bar;');
	}

	//--------------------------------------------------------------------
	public function testCloseEnough()
	{
		$this->assertCloseEnough(1, 1);
		$this->assertCloseEnough(1, 0);
		$this->assertCloseEnough(1, 2);
	}

	public function testCloseEnoughString()
	{
		$this->assertCloseEnoughString(strtotime('10:00:00'), strtotime('09:59:59'));
		$this->assertCloseEnoughString(strtotime('10:00:00'), strtotime('10:00:00'));
		$this->assertCloseEnoughString(strtotime('10:00:00'), strtotime('10:00:01'));
	}

	public function testCloseEnoughStringBadLength()
	{
		$result = $this->assertCloseEnoughString('apples & oranges', 'apples');
		$this->assertFalse($result, 'Different string lengths should have returned false');
	}

}
