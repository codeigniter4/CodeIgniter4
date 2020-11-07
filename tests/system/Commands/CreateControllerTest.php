<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

class CreateControllerTest extends CIUnitTestCase
{
	protected $streamFilter;

	protected function setUp(): void
	{
		CITestStreamFilter::$buffer = '';
		$this->streamFilter         = stream_filter_append(STDOUT, 'CITestStreamFilter');
		$this->streamFilter         = stream_filter_append(STDERR, 'CITestStreamFilter');
	}

	protected function tearDown(): void
	{
		stream_filter_remove($this->streamFilter);

		$result = str_replace(["\033[0;32m", "\033[0m", "\n"], '', CITestStreamFilter::$buffer);
		$file   = trim(substr($result, 14));
		$file   = str_replace('APPPATH' . DIRECTORY_SEPARATOR, APPPATH, $file);
		file_exists($file) && unlink($file);
	}

	protected function getBuffer(): string
	{
		return CITestStreamFilter::$buffer;
	}

	protected function getFileContents(string $filepath): string
	{
		if (! file_exists($filepath))
		{
			return '';
		}

		$contents = file_get_contents($filepath);

		return $contents ?: '';
	}

	public function testCreateControllerThatUsesBaseController()
	{
		command('make:controller user');
		$this->assertStringContainsString('Created file: ', $this->getBuffer());
		$this->assertStringContainsString('User.php', $this->getBuffer());
		$this->assertFileExists(APPPATH . 'Controllers/User.php');
		$this->assertStringContainsString('BaseController', $this->getFileContents(APPPATH . 'Controllers/User.php'));
	}

	public function testCreateControllerThatUsesCodeigniterController()
	{
		command('make:controller blog -bare');
		$this->assertStringContainsString('Created file: ', $this->getBuffer());
		$this->assertStringContainsString('Blog.php', $this->getBuffer());
		$this->assertFileExists(APPPATH . 'Controllers/Blog.php');
		$this->assertStringContainsString('use CodeIgniter\Controller;', $this->getFileContents(APPPATH . 'Controllers/Blog.php'));
	}

	public function testCreateControllerThatUsesDefaultRestfulResource()
	{
		command('make:controller api -restful');
		$this->assertStringContainsString('Created file: ', $this->getBuffer());
		$this->assertStringContainsString('Api.php', $this->getBuffer());
		$this->assertFileExists(APPPATH . 'Controllers/Api.php');
		$this->assertStringContainsString('use CodeIgniter\RESTful\ResourceController;', $this->getFileContents(APPPATH . 'Controllers/Api.php'));
	}

	public function testCreateControllerThatUsesAGivenRestfulResource()
	{
		command('make:controller api -restful presenter');
		$this->assertStringContainsString('Created file: ', $this->getBuffer());
		$this->assertStringContainsString('Api.php', $this->getBuffer());
		$this->assertFileExists(APPPATH . 'Controllers/Api.php');
		$this->assertStringContainsString('use CodeIgniter\RESTful\ResourcePresenter;', $this->getFileContents(APPPATH . 'Controllers/Api.php'));
	}

	public function testCreateRestfulControllerHasRestfulMethods()
	{
		command('make:controller pay -restful');
		$this->assertStringContainsString('Created file: ', $this->getBuffer());
		$this->assertStringContainsString('Pay.php', $this->getBuffer());
		$this->assertFileExists(APPPATH . 'Controllers/Pay.php');
		$this->assertStringContainsString('public function new()', $this->getFileContents(APPPATH . 'Controllers/Pay.php'));
	}

	public function testCreateControllerCanCreateControllersInSubfolders()
	{
		command('make:controller admin/user');
		$this->assertStringContainsString('Created file: ', $this->getBuffer());
		$this->assertStringContainsString('User.php', $this->getBuffer());
		$this->assertDirectoryExists(APPPATH . 'Controllers/Admin');
		$this->assertFileExists(APPPATH . 'Controllers/Admin/User.php');

		// cleanup
		unlink(APPPATH . 'Controllers/Admin/User.php');
		rmdir(APPPATH . 'Controllers/Admin');
	}
}
