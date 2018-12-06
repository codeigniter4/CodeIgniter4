<?php namespace CodeIgniter\Email;

class EmailTest extends \CIUnitTestCase
{

	public function testNewGoodChecked()
	{
		$path = BASEPATH . 'Common.php';
		$file = new File($path, true);
		$this->assertEquals($path, $file->getRealPath());
	}

}
