<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Test\Filters\CITestStreamFilter;
use CodeIgniter\Test\CIUnitTestCase;

class GenerateKeyTest extends CIUnitTestCase
{
	private $streamFilter;
	private $envPath;
	private $backupEnvPath;

	protected function setUp(): void
	{
		parent::setUp();

		CITestStreamFilter::$buffer = '';
		$this->streamFilter         = stream_filter_append(STDOUT, 'CITestStreamFilter');
		$this->streamFilter         = stream_filter_append(STDERR, 'CITestStreamFilter');

		$this->envPath       = ROOTPATH . '.env';
		$this->backupEnvPath = ROOTPATH . '.env.backup';

		if (file_exists($this->envPath))
		{
			rename($this->envPath, $this->backupEnvPath);
		}

		$this->resetEnvironment();
	}

	protected function tearDown(): void
	{
		stream_filter_remove($this->streamFilter);

		if (file_exists($this->envPath))
		{
			unlink($this->envPath);
		}

		if (file_exists($this->backupEnvPath))
		{
			rename($this->backupEnvPath, $this->envPath);
		}

		$this->resetEnvironment();
	}

	/**
	 * Gets buffer contents then releases it.
	 *
	 * @return string
	 */
	protected function getBuffer(): string
	{
		return CITestStreamFilter::$buffer;
	}

	protected function resetEnvironment()
	{
		putenv('encryption.key');
		unset($_ENV['encryption.key'], $_SERVER['encryption.key']);
	}

	public function testGenerateKeyShowsEncodedKey()
	{
		command('key:generate -show');
		$this->assertStringContainsString('hex2bin:', $this->getBuffer());

		command('key:generate -prefix base64 -show');
		$this->assertStringContainsString('base64:', $this->getBuffer());

		command('key:generate -prefix hex2bin -show');
		$this->assertStringContainsString('hex2bin:', $this->getBuffer());
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testGenerateKeyCreatesNewKey()
	{
		command('key:generate');
		$this->assertStringContainsString('successfully set.', $this->getBuffer());
		$this->assertStringContainsString(env('encryption.key'), file_get_contents($this->envPath));
		$this->assertStringContainsString('hex2bin:', file_get_contents($this->envPath));

		command('key:generate -prefix base64 -force');
		$this->assertStringContainsString('successfully set.', $this->getBuffer());
		$this->assertStringContainsString(env('encryption.key'), file_get_contents($this->envPath));
		$this->assertStringContainsString('base64:', file_get_contents($this->envPath));

		command('key:generate -prefix hex2bin -force');
		$this->assertStringContainsString('successfully set.', $this->getBuffer());
		$this->assertStringContainsString(env('encryption.key'), file_get_contents($this->envPath));
		$this->assertStringContainsString('hex2bin:', file_get_contents($this->envPath));
	}

	public function testDefaultShippedEnvIsMissing()
	{
		rename(ROOTPATH . 'env', ROOTPATH . 'lostenv');
		command('key:generate');
		rename(ROOTPATH . 'lostenv', ROOTPATH . 'env');

		$this->assertStringContainsString('Both default shipped', $this->getBuffer());
		$this->assertStringContainsString('Error in setting', $this->getBuffer());
	}
}
