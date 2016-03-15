<?php namespace CodeIgniter\CLI;

class CLITest extends \CIUnitTestCase
{
	public function testNew()
	{
		$actual = new CLI();
		$this->assertInstanceOf(CLI::class, $actual);
	}
}
