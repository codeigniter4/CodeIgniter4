<?php namespace CodeIgniter\Debug;

class ExceptionsTest extends \CodeIgniter\Test\CIUnitTestCase
{
	public function testNew()
	{
		$actual = new Exceptions(new \Config\Exceptions(), \Config\Services::request(), \Config\Services::response());
		$this->assertInstanceOf(Exceptions::class, $actual);
	}

	/**
	 * @dataProvider dirtyPathsProvider
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
