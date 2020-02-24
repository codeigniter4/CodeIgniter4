<?php namespace CodeIgniter\Files;

class FileTest extends \CodeIgniter\Test\CIUnitTestCase
{

	public function testNewGoodChecked()
	{
		$path = SYSTEMPATH . 'Common.php';
		$file = new File($path, true);
		$this->assertEquals($path, $file->getRealPath());
	}

	public function testNewGoodUnchecked()
	{
		$path = SYSTEMPATH . 'Common.php';
		$file = new File($path, false);
		$this->assertEquals($path, $file->getRealPath());
	}

	public function testNewBadUnchecked()
	{
		$path = SYSTEMPATH . 'bogus';
		$file = new File($path, false);
		$this->assertFalse($file->getRealPath());
	}

	public function testGuessExtension()
	{
		$file = new File(SYSTEMPATH . 'Common.php');
		$this->assertEquals('php', $file->guessExtension());
		$file = new File(SYSTEMPATH . 'index.html');
		$this->assertEquals('html', $file->guessExtension());
		$file = new File(ROOTPATH . 'phpunit.xml.dist');
		$this->assertEquals('xml', $file->guessExtension());
	}

	public function testRandomName()
	{
		$file    = new File(SYSTEMPATH . 'Common.php');
		$result1 = $file->getRandomName();
		$this->assertNotEquals($result1, $file->getRandomName());
	}

	public function testCanAccessSplFileInfoMethods()
	{
		$file = new File(SYSTEMPATH . 'Common.php');
		$this->assertEquals('file', $file->getType());
	}

	public function testGetSizeReturnsKB()
	{
		$file = new File(SYSTEMPATH . 'Common.php');
		$size = number_format(filesize(SYSTEMPATH . 'Common.php') / 1024, 3);
		$this->assertEquals($size, $file->getSizeByUnit('kb'));
	}

	public function testGetSizeReturnsMB()
	{
		$file = new File(SYSTEMPATH . 'Common.php');
		$size = number_format(filesize(SYSTEMPATH . 'Common.php') / 1024 / 1024, 3);
		$this->assertEquals($size, $file->getSizeByUnit('mb'));
	}

	public function testThrowsExceptionIfNotAFile()
	{
		$this->expectException('CodeIgniter\Files\Exceptions\FileNotFoundException');

		$file = new File(SYSTEMPATH . 'Commoner.php', true);
	}

}
