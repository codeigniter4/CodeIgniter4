<?php namespace CodeIgniter\Files;

class FileTest extends \CIUnitTestCase
{

	public function testNewGoodChecked()
	{
		$path = BASEPATH . 'Common.php';
		$file = new File($path, true);
		$this->assertEquals($path, $file->getRealPath());
	}

	public function testNewGoodUnchecked()
	{
		$path = BASEPATH . 'Common.php';
		$file = new File($path, false);
		$this->assertEquals($path, $file->getRealPath());
	}

	public function testNewBadUnchecked()
	{
		$path = BASEPATH . 'bogus';
		$file = new File($path, false);
		$this->assertFalse($file->getRealPath());
	}

	public function testGuessExtension()
	{
		$file = new File(BASEPATH . 'Common.php');
		$this->assertEquals('php', $file->guessExtension());
		$file = new File(BASEPATH . 'index.html');
		$this->assertEquals('html', $file->guessExtension());
		$file = new File(ROOTPATH . 'phpunit.xml.dist');
		$this->assertEquals('xml', $file->guessExtension());
	}

	public function testRandomName()
	{
		$file    = new File(BASEPATH . 'Common.php');
		$result1 = $file->getRandomName();
		$this->assertNotEquals($result1, $file->getRandomName());
	}

	public function testCanAccessSplFileInfoMethods()
	{
		$file = new File(BASEPATH . 'Common.php');
		$this->assertEquals('file', $file->getType());
	}

	public function testGetSizeReturnsKB()
	{
		$file = new File(BASEPATH . 'Common.php');
		$size = number_format(filesize(BASEPATH . 'Common.php') / 1024, 3);
		$this->assertEquals($size, $file->getSize('kb'));
	}

	public function testGetSizeReturnsMB()
	{
		$file = new File(BASEPATH . 'Common.php');
		$size = number_format(filesize(BASEPATH . 'Common.php') / 1024 / 1024, 3);
		$this->assertEquals($size, $file->getSize('mb'));
	}

	/**
	 * @expectedException \CodeIgniter\Files\Exceptions\FileNotFoundException
	 */
	public function testThrowsExceptionIfNotAFile()
	{
		$file = new File(BASEPATH . 'Commoner.php', true);
	}

}
