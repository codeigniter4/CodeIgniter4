<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

class CreateCommandTest extends CIUnitTestCase
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
		$dir    = dirname($file);
		file_exists($file) && unlink($file);
		is_dir($dir) && rmdir($dir);
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

	public function testCreateCommandWithDefaults()
	{
		command('make:command deliver');

		$file = APPPATH . 'Commands/Deliver.php';
		$this->assertStringContainsString('Created file: ', $this->getBuffer());
		$this->assertFileExists($file);

		$contents = $this->getFileContents($file);
		$this->assertStringContainsString('use CodeIgniter\\CLI\\BaseCommand;', $contents);
		$this->assertStringContainsString('extends BaseCommand', $contents);
		$this->assertStringContainsString('protected $group = \'CodeIgniter\';', $contents);
		$this->assertStringContainsString('protected $name = \'command:name\';', $contents);
		$this->assertStringContainsString('protected $usage = \'command:name [arguments] [options]\';', $contents);
		$this->assertStringContainsString('public function run(array $params)', $contents);
	}

	public function testCreateCommandWithGivenOptions()
	{
		command('make:command deliver -command make:deliver -type generator');
		$file = APPPATH . 'Commands/Deliver.php';
		$this->assertStringContainsString('Created file: ', $this->getBuffer());
		$this->assertFileExists($file);

		$contents = $this->getFileContents($file);
		$this->assertStringContainsString('use CodeIgniter\\CLI\\GeneratorCommand;', $contents);
		$this->assertStringContainsString('extends GeneratorCommand', $contents);
		$this->assertStringNotContainsString('protected $group = \'Generators\';', $contents);
		$this->assertStringContainsString('protected $name = \'make:deliver\';', $contents);
		$this->assertStringContainsString('protected $usage = \'make:deliver [arguments] [options]\';', $contents);
		$this->assertStringContainsString('protected function getTemplate(): string', $contents);
	}

	public function testCreateCommandWithOverridingGroupGiven()
	{
		command('make:command deliver -command make:deliver -type generator -group Deliverables');
		$file = APPPATH . 'Commands/Deliver.php';
		$this->assertStringContainsString('Created file: ', $this->getBuffer());
		$this->assertFileExists($file);

		$contents = $this->getFileContents($file);
		$this->assertStringContainsString('use CodeIgniter\\CLI\\GeneratorCommand;', $contents);
		$this->assertStringContainsString('extends GeneratorCommand', $contents);
		$this->assertStringContainsString('protected $group = \'Deliverables\';', $contents);
		$this->assertStringContainsString('protected $name = \'make:deliver\';', $contents);
		$this->assertStringContainsString('protected $usage = \'make:deliver [arguments] [options]\';', $contents);
		$this->assertStringContainsString('protected function getTemplate(): string', $contents);
	}
}
