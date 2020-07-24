<?php

namespace CodeIgniter\Commands;

use CodeIgniter\CLI\CommandRunner;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

class GenerateKeyTest extends CIUnitTestCase
{
	private $streamFilter;
	private $envPath;
	private $backupEnvPath;

	/**
	 * @var CommandRunner
	 */
	private $runner;

	protected function setUp(): void
	{
		parent::setUp();

		CITestStreamFilter::$buffer = '';
		$this->streamFilter         = stream_filter_append(STDOUT, 'CITestStreamFilter');
		$this->streamFilter         = stream_filter_append(STDERR, 'CITestStreamFilter');

		$this->runner = new CommandRunner();
		$this->runner->initController(Services::request(), Services::response(), Services::logger());

		$this->envPath       = ROOTPATH . '.env';
		$this->backupEnvPath = ROOTPATH . '.env.backup';

		if (file_exists($this->envPath))
		{
			rename($this->envPath, $this->backupEnvPath);
		}
	}

	protected function tearDown(): void
	{
		stream_filter_remove($this->streamFilter);

		if (file_exists($this->envPath))
		{
			unlink($this->envPath);
		}

		rename($this->backupEnvPath, $this->envPath);
	}

	/**
	 * Gets buffer contents then releases it.
	 *
	 * @return string
	 */
	protected function getBuffer(): string
	{
		$result                     = CITestStreamFilter::$buffer;
		CITestStreamFilter::$buffer = '';

		return $result;
	}

	public function testGenerateKeyShowsEncodedKey()
	{
		$this->runner->index(['key:generate', '-encoding' => 'hex', '-show' => null]);
		$this->assertStringContainsString('hex2bin:', $this->getBuffer());

		$this->runner->index(['key:generate', '-encoding' => 'base64', '-show' => null]);
		$this->assertStringContainsString('base64:', $this->getBuffer());
	}

	public function testGenerateKeyCreatesNewKey()
	{
		// use the 'force' option to bypass CLI::prompt
		$this->runner->index(['key:generate', '-encoding' => 'hex', '-force' => null]);
		$this->assertStringContainsString('successfully set.', $this->getBuffer());
		$this->assertStringContainsString(env('encryption.key'), file_get_contents($this->envPath));
	}
}
