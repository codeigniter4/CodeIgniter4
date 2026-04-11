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

namespace CodeIgniter\Commands;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[CoversClass(ListCommands::class)]
#[Group('Others')]
final class ListCommandsTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    #[After]
    #[Before]
    protected function resetCli(): void
    {
        CLI::reset();
    }

    public function testRunCommand(): void
    {
        command('list');

        $this->assertStringContainsString('cache:clear', $this->getStreamFilterBuffer());
        $this->assertStringContainsString('Clears the current system caches.', $this->getStreamFilterBuffer());
    }

    public function testRunCommandWithSimpleOption(): void
    {
        command('list --simple');

        $this->assertStringContainsString('cache:clear', $this->getStreamFilterBuffer());
        $this->assertStringNotContainsString('Clears the current system caches.', $this->getStreamFilterBuffer());
    }
}
