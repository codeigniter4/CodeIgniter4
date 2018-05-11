<?php namespace CodeIgniter\Files;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class FileWithVfsTest extends \CIUnitTestCase
{

	private $root;

	//--------------------------------------------------------------------

	public function setup()
	{
		$this->root = vfsStream::setup();
		$this->path = '_support/Files/';
		vfsStream::copyFromFileSystem(TESTPATH . $this->path, $root);
		$this->start = $this->root->url() . '/';
		$this->file = new File($this->start . 'able/apple.php');
	}

	//---------------------------------------------------------------
	public function testDestinationUnknown()
	{
		$destination = $this->start . 'charlie/cherry.php';
		$this->assertEquals($destination, $this->file->getDestination($destination));
	}

	public function testDestinationSameFileSameFolder()
	{
		$destination = $this->start . 'able/apple.php';
		$this->assertEquals($this->start . 'able/apple_1.php', $this->file->getDestination($destination));
	}

	public function testDestinationSameFileDifferentFolder()
	{
		$destination = $this->start . 'baker/apple.php';
		$this->assertEquals($destination, $this->file->getDestination($destination));
	}

	public function testDestinationDifferentFileSameFolder()
	{
		$destination = $this->start . 'able/date.php';
		$this->assertEquals($destination, $this->file->getDestination($destination));
	}

	public function testDestinationDifferentFileDifferentFolder()
	{
		$destination = $this->start . 'baker/date.php';
		$this->assertEquals($destination, $this->file->getDestination($destination));
	}

	public function testDestinationExistingFileDifferentFolder()
	{
		$destination = $this->start . 'baker/banana.php';
		$this->assertEquals($this->start . 'baker/banana_1.php', $this->file->getDestination($destination));
	}

	public function testDestinationDelimited()
	{
		$destination = $this->start . 'able/fig_3.php';
		$this->assertEquals($this->start . 'able/fig_4.php', $this->file->getDestination($destination));
	}

	public function testDestinationDelimitedAlpha()
	{
		$destination = $this->start . 'able/prune_ripe.php';
		$this->assertEquals($this->start . 'able/prune_ripe_1.php', $this->file->getDestination($destination));
	}

	//---------------------------------------------------------------
	public function testMoveNormal()
	{
		$destination = $this->start . 'baker';
		$this->file->move($destination);
		$this->assertTrue($this->root->hasChild('baker/apple.php'));
		$this->assertFalse($this->root->hasChild('able/apple.php'));
	}

	public function testMoveRename()
	{
		$destination = $this->start . 'baker';
		$this->file->move($destination, 'popcorn.php');
		$this->assertTrue($this->root->hasChild('baker/popcorn.php'));
		$this->assertFalse($this->root->hasChild('able/apple.php'));
	}

//	public function testMoveOverwrite()
//	{
//		
//	}
//
//	/**
//	 * @expectedException FileException
//	 */
//	public function testMoveFailure()
//	{
//		$this->root->
//		$destination = $this->start . 'baker';
//		$this->file->move($destination);
//	}

}
