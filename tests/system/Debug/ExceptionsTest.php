<?php

namespace CodeIgniter\Debug;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Exceptions as ExceptionConfig;
use Config\Services;

class ExceptionsTest extends CIUnitTestCase
{
	public function testNew()
	{
		$actual = new Exceptions(new ExceptionConfig(), Services::request(), Services::response());
		$this->assertInstanceOf(Exceptions::class, $actual);
	}

	/**
	 * @dataProvider dirtyPathsProvider
	 *
	 * @param mixed $file
	 * @param mixed $expected
	 */
	public function testCleanPaths($file, $expected)
	{
		$this->assertEquals($expected, Exceptions::cleanPath($file));
	}

	public function dirtyPathsProvider()
	{
		$ds = DIRECTORY_SEPARATOR;

		return [
			[
				APPPATH . 'Config' . $ds . 'App.php',
				'APPPATH' . $ds . 'Config' . $ds . 'App.php',
			],
			[
				SYSTEMPATH . 'CodeIgniter.php',
				'SYSTEMPATH' . $ds . 'CodeIgniter.php',
			],
			[
				VENDORPATH . 'autoload.php',
				'VENDORPATH' . $ds . 'autoload.php',
			],
			[
				FCPATH . 'index.php',
				'FCPATH' . $ds . 'index.php',
			],
		];
	}
}
