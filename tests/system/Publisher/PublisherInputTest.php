<?php

use CodeIgniter\Publisher\Exceptions\PublisherException;
use CodeIgniter\Publisher\Publisher;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Publishers\TestPublisher;

class PublisherInputTest extends CIUnitTestCase
{
	/**
	 * A known, valid file
	 *
	 * @var string
	 */
	private $file = SUPPORTPATH . 'Files/baker/banana.php';

	/**
	 * A known, valid directory
	 *
	 * @var string
	 */
	private $directory = SUPPORTPATH . 'Files/able/';

	/**
	 * Initialize the helper, since some
	 * tests call static methods before
	 * the constructor would load it.
	 */
	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		helper(['filesystem']);
	}

	//--------------------------------------------------------------------

	public function testAddFile()
	{
		$publisher = new Publisher();
		$this->assertSame([], $this->getPrivateProperty($publisher, 'files'));

		$publisher->addFile($this->file);
		$this->assertSame([$this->file], $this->getPrivateProperty($publisher, 'files'));
	}

	public function testAddFileMissing()
	{
		$publisher = new Publisher();

		$this->expectException(PublisherException::class);
		$this->expectExceptionMessage(lang('Publisher.expectedFile', ['addFile']));

		$publisher->addFile('TheHillsAreAlive.bmp');
	}

	public function testAddFileDirectory()
	{
		$publisher = new Publisher();

		$this->expectException(PublisherException::class);
		$this->expectExceptionMessage(lang('Publisher.expectedFile', ['addFile']));

		$publisher->addFile($this->directory);
	}

	public function testAddFiles()
	{
		$publisher = new Publisher();
		$files     = [
			$this->file,
			$this->file,
		];

		$publisher->addFiles($files);
		$this->assertSame($files, $this->getPrivateProperty($publisher, 'files'));
	}

	//--------------------------------------------------------------------

	public function testGetFiles()
	{
		$publisher = new Publisher();
		$publisher->addFile($this->file);

		$this->assertSame([$this->file], $publisher->getFiles());
	}

	public function testGetFilesSorts()
	{
		$publisher = new Publisher();
		$files     = [
			$this->file,
			$this->directory . 'apple.php',
		];

		$publisher->addFiles($files);

		$this->assertSame(array_reverse($files), $publisher->getFiles());
	}

	public function testGetFilesUniques()
	{
		$publisher = new Publisher();
		$files     = [
			$this->file,
			$this->file,
		];

		$publisher->addFiles($files);
		$this->assertSame([$this->file], $publisher->getFiles());
	}

	public function testSetFiles()
	{
		$publisher = new Publisher();

		$publisher->setFiles([$this->file]);
		$this->assertSame([$this->file], $publisher->getFiles());
	}

	public function testSetFilesInvalid()
	{
		$publisher = new Publisher();

		$this->expectException(PublisherException::class);
		$this->expectExceptionMessage(lang('Publisher.expectedFile', ['addFile']));

		$publisher->setFiles(['flerb']);
	}

	//--------------------------------------------------------------------

	public function testRemoveFile()
	{
		$publisher = new Publisher();
		$files     = [
			$this->file,
			$this->directory . 'apple.php',
		];

		$publisher->addFiles($files);

		$publisher->removeFile($this->file);

		$this->assertSame([$this->directory . 'apple.php'], $publisher->getFiles());
	}

	public function testRemoveFiles()
	{
		$publisher = new Publisher();
		$files     = [
			$this->file,
			$this->directory . 'apple.php',
		];

		$publisher->addFiles($files);

		$publisher->removeFiles($files);

		$this->assertSame([], $publisher->getFiles());
	}

	//--------------------------------------------------------------------

	public function testAddDirectoryInvalid()
	{
		$publisher = new Publisher();

		$this->expectException(PublisherException::class);
		$this->expectExceptionMessage(lang('Publisher.expectedDirectory', ['addDirectory']));

		$publisher->addDirectory($this->file);
	}

	public function testAddDirectory()
	{
		$publisher = new Publisher();
		$expected  = [
			$this->directory . 'apple.php',
			$this->directory . 'fig_3.php',
			$this->directory . 'prune_ripe.php',
		];

		$publisher->addDirectory($this->directory);

		$this->assertSame($expected, $publisher->getFiles());
	}

	public function testAddDirectoryRecursive()
	{
		$publisher = new Publisher();
		$expected  = [
			$this->directory . 'apple.php',
			$this->directory . 'fig_3.php',
			$this->directory . 'prune_ripe.php',
			SUPPORTPATH . 'Files/baker/banana.php',
		];

		$publisher->addDirectory(SUPPORTPATH . 'Files', true);

		$this->assertSame($expected, $publisher->getFiles());
	}

	public function testAddDirectories()
	{
		$publisher = new Publisher();
		$expected  = [
			$this->directory . 'apple.php',
			$this->directory . 'fig_3.php',
			$this->directory . 'prune_ripe.php',
			SUPPORTPATH . 'Files/baker/banana.php',
		];

		$publisher->addDirectories([
			$this->directory,
			SUPPORTPATH . 'Files/baker',
		]);

		$this->assertSame($expected, $publisher->getFiles());
	}

	public function testAddDirectoriesRecursive()
	{
		$publisher = new Publisher();
		$expected  = [
			$this->directory . 'apple.php',
			$this->directory . 'fig_3.php',
			$this->directory . 'prune_ripe.php',
			SUPPORTPATH . 'Files/baker/banana.php',
			SUPPORTPATH . 'Log/Handlers/TestHandler.php',
		];

		$publisher->addDirectories([
			SUPPORTPATH . 'Files',
			SUPPORTPATH . 'Log',
		], true);

		$this->assertSame($expected, $publisher->getFiles());
	}

	//--------------------------------------------------------------------

	public function testAddPathFile()
	{
		$publisher = new Publisher(SUPPORTPATH . 'Files');

		$publisher->addPath('baker/banana.php');

		$this->assertSame([$this->file], $publisher->getFiles());
	}

	public function testAddPathFileRecursiveDoesNothing()
	{
		$publisher = new Publisher(SUPPORTPATH . 'Files');

		$publisher->addPath('baker/banana.php', true);

		$this->assertSame([$this->file], $publisher->getFiles());
	}

	public function testAddPathDirectory()
	{
		$publisher = new Publisher(SUPPORTPATH . 'Files');

		$expected = [
			$this->directory . 'apple.php',
			$this->directory . 'fig_3.php',
			$this->directory . 'prune_ripe.php',
		];

		$publisher->addPath('able');

		$this->assertSame($expected, $publisher->getFiles());
	}

	public function testAddPathDirectoryRecursive()
	{
		$publisher = new Publisher(SUPPORTPATH);

		$expected = [
			$this->directory . 'apple.php',
			$this->directory . 'fig_3.php',
			$this->directory . 'prune_ripe.php',
			SUPPORTPATH . 'Files/baker/banana.php',
		];

		$publisher->addPath('Files');

		$this->assertSame($expected, $publisher->getFiles());
	}

	public function testAddPaths()
	{
		$publisher = new Publisher(SUPPORTPATH . 'Files');

		$expected = [
			$this->directory . 'apple.php',
			$this->directory . 'fig_3.php',
			$this->directory . 'prune_ripe.php',
			SUPPORTPATH . 'Files/baker/banana.php',
		];

		$publisher->addPaths([
			'able',
			'baker/banana.php',
		]);

		$this->assertSame($expected, $publisher->getFiles());
	}

	public function testAddPathsRecursive()
	{
		$publisher = new Publisher(SUPPORTPATH);

		$expected = [
			$this->directory . 'apple.php',
			$this->directory . 'fig_3.php',
			$this->directory . 'prune_ripe.php',
			SUPPORTPATH . 'Files/baker/banana.php',
			SUPPORTPATH . 'Log/Handlers/TestHandler.php',
		];

		$publisher->addPaths([
			'Files',
			'Log',
		], true);

		$this->assertSame($expected, $publisher->getFiles());
	}

	//--------------------------------------------------------------------

	public function testAddUri()
	{
		$publisher = new Publisher();
		$publisher->addUri('https://raw.githubusercontent.com/codeigniter4/CodeIgniter4/develop/composer.json');

		$scratch = $this->getPrivateProperty($publisher, 'scratch');

		$this->assertSame([$scratch . 'composer.json'], $publisher->getFiles());
	}

	public function testAddUris()
	{
		$publisher = new Publisher();
		$publisher->addUris([
			'https://raw.githubusercontent.com/codeigniter4/CodeIgniter4/develop/LICENSE',
			'https://raw.githubusercontent.com/codeigniter4/CodeIgniter4/develop/composer.json',
		]);

		$scratch = $this->getPrivateProperty($publisher, 'scratch');

		$this->assertSame([$scratch . 'LICENSE', $scratch . 'composer.json'], $publisher->getFiles());
	}

	//--------------------------------------------------------------------

	public function testRemovePatternEmpty()
	{
		$publisher = new Publisher();
		$publisher->addDirectory(SUPPORTPATH . 'Files', true);

		$files = $publisher->getFiles();

		$publisher->removePattern('');

		$this->assertSame($files, $publisher->getFiles());
	}

	public function testRemovePatternRegex()
	{
		$publisher = new Publisher();
		$publisher->addDirectory(SUPPORTPATH . 'Files', true);

		$expected = [
			$this->directory . 'apple.php',
			SUPPORTPATH . 'Files/baker/banana.php',
		];

		$publisher->removePattern('#[a-z]+_.*#');

		$this->assertSame($expected, $publisher->getFiles());
	}

	public function testRemovePatternPseudo()
	{
		$publisher = new Publisher();
		$publisher->addDirectory(SUPPORTPATH . 'Files', true);

		$expected = [
			$this->directory . 'apple.php',
			SUPPORTPATH . 'Files/baker/banana.php',
		];

		$publisher->removePattern('*_*.php');

		$this->assertSame($expected, $publisher->getFiles());
	}

	public function testRemovePatternScope()
	{
		$publisher = new Publisher();
		$publisher->addDirectory(SUPPORTPATH . 'Files', true);

		$expected = [
			SUPPORTPATH . 'Files/baker/banana.php',
		];

		$publisher->removePattern('*.php', $this->directory);

		$this->assertSame($expected, $publisher->getFiles());
	}

	//--------------------------------------------------------------------

	public function testRetainPatternEmpty()
	{
		$publisher = new Publisher();
		$publisher->addDirectory(SUPPORTPATH . 'Files', true);

		$files = $publisher->getFiles();

		$publisher->retainPattern('');

		$this->assertSame($files, $publisher->getFiles());
	}

	public function testRetainPatternRegex()
	{
		$publisher = new Publisher();
		$publisher->addDirectory(SUPPORTPATH . 'Files', true);

		$expected = [
			$this->directory . 'fig_3.php',
			$this->directory . 'prune_ripe.php',
		];

		$publisher->retainPattern('#[a-z]+_.*#');

		$this->assertSame($expected, $publisher->getFiles());
	}

	public function testRetainPatternPseudo()
	{
		$publisher = new Publisher();
		$publisher->addDirectory(SUPPORTPATH . 'Files', true);

		$expected = [
			$this->directory . 'fig_3.php',
		];

		$publisher->retainPattern('*_?.php');

		$this->assertSame($expected, $publisher->getFiles());
	}

	public function testRetainPatternScope()
	{
		$publisher = new Publisher();
		$publisher->addDirectory(SUPPORTPATH . 'Files', true);

		$expected = [
			$this->directory . 'fig_3.php',
			SUPPORTPATH . 'Files/baker/banana.php',
		];

		$publisher->retainPattern('*_?.php', $this->directory);

		$this->assertSame($expected, $publisher->getFiles());
	}
}
