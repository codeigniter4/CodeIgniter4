<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;

/**
 * @internal
 *
 * @group Others
 */
final class HelpCommandTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function getBuffer()
    {
        return $this->getStreamFilterBuffer();
    }

    public function testHelpCommand(): void
    {
        command('help');

        // make sure the result looks like a command list
        $this->assertStringContainsString('Displays basic usage information.', $this->getBuffer());
        $this->assertStringContainsString('command_name', $this->getBuffer());
    }

    public function testHelpCommandWithMissingUsage(): void
    {
        command('help app:info');
        $this->assertStringContainsString('app:info [arguments]', $this->getBuffer());
    }

    public function testHelpCommandOnSpecificCommand(): void
    {
        command('help cache:clear');
        $this->assertStringContainsString('Clears the current system caches.', $this->getBuffer());
    }

    public function testHelpCommandOnInexistentCommand(): void
    {
        command('help fixme');
        $this->assertStringContainsString('Command "fixme" not found', $this->getBuffer());
    }

    public function testHelpCommandOnInexistentCommandButWithAlternatives(): void
    {
        command('help clear');
        $this->assertStringContainsString('Command "clear" not found.', $this->getBuffer());
        $this->assertStringContainsString('Did you mean one of these?', $this->getBuffer());
    }
}
