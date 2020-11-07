<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

class CreateModelTest extends CIUnitTestCase
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

	public function testCreateModelWithDefaults()
	{
		command('make:model user');
		$this->assertStringContainsString('Created file: ', $this->getBuffer());

		$file = APPPATH . 'Models/User.php';
		$this->assertFileExists($file);
		$this->assertStringContainsString('extends Model', $this->getFileContents($file));
		$this->assertStringContainsString('protected $table      = \'users\';', $this->getFileContents($file));
		$this->assertStringContainsString('protected $DBGroup  = \'default\';', $this->getFileContents($file));
		$this->assertStringContainsString('protected $returnType     = \'array\';', $this->getFileContents($file));
	}

	public function testCreateModelWithDbGroup()
	{
		command('make:model user -dbgroup testing');
		$this->assertStringContainsString('Created file: ', $this->getBuffer());

		$file = APPPATH . 'Models/User.php';
		$this->assertFileExists($file);
		$this->assertStringContainsString('protected $DBGroup  = \'testing\';', $this->getFileContents($file));
	}

	public function testCreateModelWithEntityAsReturnType()
	{
		command('make:model user -entity');
		$this->assertStringContainsString('Created file: ', $this->getBuffer());

		$file = APPPATH . 'Models/User.php';
		$this->assertFileExists($file);
		$this->assertStringContainsString('protected $returnType     = \'App\\Entities\\User\';', $this->getFileContents($file));
	}

	public function testCreateModelWithDifferentModelTable()
	{
		command('make:model user -table utilisateur');
		$this->assertStringContainsString('Created file: ', $this->getBuffer());

		$file = APPPATH . 'Models/User.php';
		$this->assertFileExists($file);
		$this->assertStringContainsString('protected $table      = \'utilisateurs\';', $this->getFileContents($file));
	}

	public function testCreateModelWithEntityAndNameHasModelWillTrimInEntity()
	{
		command('make:model userModel -entity');
		$this->assertStringContainsString('Created file: ', $this->getBuffer());

		$file = APPPATH . 'Models/UserModel.php';
		$this->assertFileExists($file);
		$this->assertStringContainsString('protected $returnType     = \'App\\Entities\\User\';', $this->getFileContents($file));
	}
}
