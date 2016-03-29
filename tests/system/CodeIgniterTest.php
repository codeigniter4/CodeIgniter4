<?php namespace CodeIgniter;

use Config\App;

class CodeIgniterTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var \CodeIgniter\CodeIgniter
	 */
	protected $codeigniter;

	//--------------------------------------------------------------------

	public function setUp()
	{
		$config = new App();
		$this->codeigniter = new MockCodeIgniter(memory_get_usage(), microtime(true), $config);
	}

	//--------------------------------------------------------------------

	public function testRunDefaultRoute()
	{
		$_SERVER['argv'] = [
			'index.php',
			'/',
		];
		$_SERVER['argc'] = 2;

		ob_start();
		$this->codeigniter->run();
		$output = ob_get_clean();

		$this->assertContains('<h1>Welcome to CodeIgniter</h1>', $output);
	}

	//--------------------------------------------------------------------

}
