<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

class HelpCommandTest extends CIUnitTestCase
{
	private $streamFilter;

	protected function setUp(): void
	{
		parent::setUp();

		CITestStreamFilter::$buffer = '';
		$this->streamFilter         = stream_filter_append(STDOUT, 'CITestStreamFilter');
		$this->streamFilter         = stream_filter_append(STDERR, 'CITestStreamFilter');
	}

	protected function tearDown(): void
	{
		stream_filter_remove($this->streamFilter);
	}

	protected function getBuffer()
	{
		return CITestStreamFilter::$buffer;
	}

	public function testHelpCommand()
	{
		command('help');

		// make sure the result looks like a command list
		$this->assertStringContainsString('Displays basic usage information.', $this->getBuffer());
		$this->assertStringContainsString('command_name', $this->getBuffer());
	}

	public function testHelpCommandWithMissingUsage()
	{
		command('help app:info');
		$this->assertStringContainsString('app:info [arguments]', $this->getBuffer());
	}

	public function testHelpCommandOnSpecificCommand()
	{
		command('help cache:clear');
		$this->assertStringContainsString('Clears the current system caches.', $this->getBuffer());
	}

	public function testHelpCommandOnInexistentCommand()
	{
		command('help fixme');
		$this->assertStringContainsString('Command "fixme" not found', $this->getBuffer());
	}

	public function testHelpCommandOnInexistentCommandButWithAlternatives()
	{
		command('help clear');
		$this->assertStringContainsString('Command "clear" not found.', $this->getBuffer());
		$this->assertStringContainsString('Did you mean one of these?', $this->getBuffer());
	}
}
