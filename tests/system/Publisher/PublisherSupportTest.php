<?php namespace CodeIgniter\Pager;

use CodeIgniter\Publisher\Exceptions\PublisherException;
use CodeIgniter\Publisher\Publisher;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Publishers\TestPublisher;

class PublisherSupportTest extends CIUnitTestCase
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

	public function testDiscoverDefault()
	{
		$result = Publisher::discover();

		$this->assertCount(1, $result);
		$this->assertInstanceOf(TestPublisher::class, $result[0]);
	}

	public function testDiscoverNothing()
	{
		$result = Publisher::discover('Nothing');

		$this->assertSame([], $result);
	}

	public function testDiscoverStores()
	{
		$publisher = Publisher::discover()[0];
		$publisher->addFile($this->file);

		$result = Publisher::discover();
		$this->assertSame($publisher, $result[0]);
		$this->assertSame([$this->file], $result[0]->getFiles());
	}

	//--------------------------------------------------------------------

	public function testResolveDirectoryDirectory()
	{
		$method = $this->getPrivateMethodInvoker(Publisher::class, 'resolveDirectory');

		$this->assertSame($this->directory, $method($this->directory));
	}

	public function testResolveDirectoryFile()
	{
		$method = $this->getPrivateMethodInvoker(Publisher::class, 'resolveDirectory');

		$this->expectException(PublisherException::class);
		$this->expectExceptionMessage(lang('Publisher.expectedDirectory', ['invokeArgs']));

		$method($this->file);
	}

	public function testResolveDirectorySymlink()
	{
		// Create a symlink to test
		$link = sys_get_temp_dir() . DIRECTORY_SEPARATOR . bin2hex(random_bytes(4));
		symlink($this->directory, $link);

		$method = $this->getPrivateMethodInvoker(Publisher::class, 'resolveDirectory');

		$this->assertSame($this->directory, $method($link));

		unlink($link);
	}

	//--------------------------------------------------------------------

	public function testResolveFileFile()
	{
		$method = $this->getPrivateMethodInvoker(Publisher::class, 'resolveFile');

		$this->assertSame($this->file, $method($this->file));
	}

	public function testResolveFileSymlink()
	{
		// Create a symlink to test
		$link = sys_get_temp_dir() . DIRECTORY_SEPARATOR . bin2hex(random_bytes(4));
		symlink($this->file, $link);

		$method = $this->getPrivateMethodInvoker(Publisher::class, 'resolveFile');

		$this->assertSame($this->file, $method($link));

		unlink($link);
	}

	public function testResolveFileDirectory()
	{
		$method = $this->getPrivateMethodInvoker(Publisher::class, 'resolveFile');

		$this->expectException(PublisherException::class);
		$this->expectExceptionMessage(lang('Publisher.expectedFile', ['invokeArgs']));

		$method($this->directory);
	}

	//--------------------------------------------------------------------

	public function testGetSource()
	{
		$publisher = new Publisher(ROOTPATH);

		$this->assertSame(ROOTPATH, $publisher->getSource());
	}

	public function testGetDestination()
	{
		$publisher = new Publisher(ROOTPATH, SUPPORTPATH);

		$this->assertSame(SUPPORTPATH, $publisher->getDestination());
	}

	public function testGetScratch()
	{
		$publisher = new Publisher();
		$this->assertNull($this->getPrivateProperty($publisher, 'scratch'));

		$scratch = $publisher->getScratch();

		$this->assertIsString($scratch);
		$this->assertDirectoryExists($scratch);
		$this->assertDirectoryIsWritable($scratch);
		$this->assertNotNull($this->getPrivateProperty($publisher, 'scratch'));

		// Directory and contents should be removed on __destruct()
		$file = $scratch . 'obvious_statement.txt';
		file_put_contents($file, 'Bananas are a most peculiar fruit');

		$publisher->__destruct();

		$this->assertFileDoesNotExist($file);
		$this->assertDirectoryDoesNotExist($scratch);
	}

	public function testGetErrors()
	{
		$publisher = new Publisher();
		$this->assertSame([], $publisher->getErrors());

		$expected = [
			$this->file => PublisherException::forCollision($this->file, $this->file),
		];

		$this->setPrivateProperty($publisher, 'errors', $expected);

		$this->assertSame($expected, $publisher->getErrors());
	}

	//--------------------------------------------------------------------

	public function testWipeDirectory()
	{
		$directory = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . bin2hex(random_bytes(6));
		mkdir($directory, 0700);
		$this->assertDirectoryExists($directory);

		$method = $this->getPrivateMethodInvoker(Publisher::class, 'wipeDirectory');
		$method($directory);

		$this->assertDirectoryDoesNotExist($directory);
	}

	public function testWipeIgnoresFiles()
	{
		$method = $this->getPrivateMethodInvoker(Publisher::class, 'wipeDirectory');
		$method($this->file);

		$this->assertFileExists($this->file);
	}

	public function testWipe()
	{
		$directory = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . bin2hex(random_bytes(6));
		mkdir($directory, 0700);
		$this->assertDirectoryExists($directory);

		$publisher = new Publisher($this->directory, $directory);
		$publisher->wipe();

		$this->assertDirectoryDoesNotExist($directory);
	}
}
