<?php namespace CodeIgniter\Test;

use CodeIgniter\Events\Events;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use CodeIgniter\Test\Filters\CITestKeyboardFilter;

class TestTest extends \CIUnitTestCase
{

	public function testGetPrivatePropertyWithObject()
	{
		$obj = new __TestForReflectionHelper();
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
		Events::on('foo', function($arg) use(&$result) {
			$result = $arg;
		});

		Events::trigger('foo', 'bar');

		$this->assertEventTriggered('foo');
	}

	//--------------------------------------------------------------------

	public function testStreamFilter()
	{
		CITestStreamFilter::$buffer = '';
		$this->stream_filter = stream_filter_append(STDOUT, 'CITestStreamFilter');
		\CodeIgniter\CLI\CLI::write('first.');
		$expected = "first.\n";
		$this->assertEquals($expected, CITestStreamFilter::$buffer);
		stream_filter_remove($this->stream_filter);
	}


}
