<?php namespace CodeIgniter\Files;

class FileTest extends \CIUnitTestCase {

	public function testCanAccessSPLFileInfoMethods()
	{
		$file = new File(BASEPATH.'Common.php');

		$this->assertEquals('file', $file->getType());
	}

	public function testGetSizeReturnsKB()
	{
		$file = new File(BASEPATH.'Common.php');

		$size = number_format(filesize(BASEPATH.'Common.php') / 1024, 3);

		$this->assertEquals($size, $file->getSize('kb'));
	}

	public function testGetSizeReturnsMB()
	{
		$file = new File(BASEPATH.'Common.php');

		$size = number_format(filesize(BASEPATH.'Common.php') / 1024 / 1024, 3);

		$this->assertEquals($size, $file->getSize('mb'));
	}

	/**
	 * @expectedException \CodeIgniter\Files\FileNotFoundException
	 */
	public function testThrowsExceptionIfNotAFile()
	{
		$file = new File(BASEPATH.'Commoner.php',true);
	}

}
