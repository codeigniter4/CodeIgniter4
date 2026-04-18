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
use CodeIgniter\CLI\Commands;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Duplicates\DuplicateLegacy;
use Tests\Support\Duplicates\DuplicateModern;

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
    protected function resetAll(): void
    {
        $this->resetServices();

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

    public function testDuplicateCommandNameListedOnceInSimpleOutput(): void
    {
        $list = new ListCommands($this->mockRunnerWithDuplicate());

        $list->run([], ['simple' => null]);

        $this->assertSame(1, substr_count($this->getStreamFilterBuffer(), "dup:test\n"));
    }

    public function testDuplicateCommandNameShowsLegacyDescriptionInDetailedOutput(): void
    {
        $list = new ListCommands($this->mockRunnerWithDuplicate());

        $list->run([], []);

        $buffer = $this->getStreamFilterBuffer();

        $this->assertStringContainsString('Legacy dup description', $buffer);
        $this->assertStringNotContainsString('Modern dup description', $buffer);
    }

    private function mockRunnerWithDuplicate(): Commands
    {
        $runner = $this->createMock(Commands::class);
        $runner->expects($this->once())
            ->method('getCommands')
            ->willReturn([
                'dup:test' => [
                    'class'       => DuplicateLegacy::class,
                    'file'        => 'irrelevant',
                    'group'       => 'Duplicates',
                    'description' => 'Legacy dup description',
                ],
            ]);
        $runner->expects($this->once())
            ->method('getModernCommands')
            ->willReturn([
                'dup:test' => [
                    'class'       => DuplicateModern::class,
                    'file'        => 'irrelevant',
                    'group'       => 'Duplicates',
                    'description' => 'Modern dup description',
                ],
            ]);

        return $runner;
    }
}
