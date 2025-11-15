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

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class CommandOverrideTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        $this->resetServices();

        parent::setUp();
    }

    protected function getBuffer(): string
    {
        return $this->getStreamFilterBuffer();
    }

    public function testOverrideListCommands(): void
    {
        $this->copyListCommands();

        command('list');

        $this->assertStringContainsString('This is App\Commands\ListCommands', $this->getBuffer());
        $this->assertStringNotContainsString('Displays basic usage information.', $this->getBuffer());

        $this->deleteListCommands();
    }

    private function copyListCommands(): void
    {
        if (! is_dir(APPPATH . 'Commands')) {
            mkdir(APPPATH . 'Commands');
        }
        copy(SUPPORTPATH . '_command/ListCommands.php', APPPATH . 'Commands/ListCommands.php');
    }

    private function deleteListCommands(): void
    {
        unlink(APPPATH . 'Commands/ListCommands.php');
    }
}
