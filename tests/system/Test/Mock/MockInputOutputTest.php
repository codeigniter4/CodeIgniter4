<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Mock;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class MockInputOutputTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function tearDown(): void
    {
        parent::tearDown();

        CLI::resetInputOutput();
    }

    public function testFwriteThroughMockPreservesEnclosingStreamFilter(): void
    {
        CLI::write('before mock');
        $this->assertStringContainsString('before mock', $this->getStreamFilterBuffer());

        $io = new MockInputOutput();
        CLI::setInputOutput($io);
        CLI::write('through mock');
        CLI::resetInputOutput();

        // The mock captured its own write into its own buffer...
        $this->assertStringContainsString('through mock', $io->getOutput());
        // ...and left the enclosing StreamFilterTrait buffer untouched.
        $this->assertStringNotContainsString('through mock', $this->getStreamFilterBuffer());

        // The enclosing filter is still attached, so later writes are captured.
        CLI::write('after mock');
        $this->assertStringContainsString('before mock', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('after mock', $this->getStreamFilterBuffer());
    }

    public function testInputThroughMockPreservesEnclosingStreamFilter(): void
    {
        $io = new MockInputOutput();
        $io->setInputs(['y']);
        CLI::setInputOutput($io);
        CLI::prompt('Continue?', ['y', 'n']);
        CLI::resetInputOutput();

        CLI::write('after prompt');
        $this->assertStringContainsString('after prompt', $this->getStreamFilterBuffer());
    }
}
