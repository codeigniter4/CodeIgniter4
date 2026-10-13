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

namespace CodeIgniter\CLI;

use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockInputOutput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[CoversClass(ProgressBar::class)]
#[Group('Others')]
final class ProgressBarTest extends CIUnitTestCase
{
    private MockInputOutput $io;
    private ProgressBar $bar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->io  = new MockInputOutput();
        $this->bar = new ProgressBar(new Output($this->io));
    }

    public function testRedrawsInPlaceWhileAdvancing(): void
    {
        $this->bar->advanceTo(1, 20);
        $this->bar->advanceTo(10, 20);
        $this->bar->advanceTo(20, 20);

        $this->assertSame(
            "[\033[32m#.........\033[0m]   5% Complete" . PHP_EOL
            . "\033[1A[\033[32m#####.....\033[0m]  50% Complete" . PHP_EOL
            . "\033[1A[\033[32m##########\033[0m] 100% Complete" . PHP_EOL,
            $this->io->getOutput(),
        );
    }

    public function testGoingBackwardsStartsANewBar(): void
    {
        $this->bar->advanceTo(10, 20);
        $this->bar->advanceTo(1, 20);

        $this->assertSame(
            "[\033[32m#####.....\033[0m]  50% Complete" . PHP_EOL
            . "[\033[32m#.........\033[0m]   5% Complete" . PHP_EOL,
            $this->io->getOutput(),
        );
    }

    public function testClearRingsTheBellAndStartsANewBar(): void
    {
        $this->bar->advanceTo(1, 20);
        $this->bar->clear();
        $this->bar->clear();
        $this->bar->advanceTo(1, 20);

        $this->assertSame(
            "[\033[32m#.........\033[0m]   5% Complete" . PHP_EOL
            . "\x07\x07"
            . "[\033[32m#.........\033[0m]   5% Complete" . PHP_EOL,
            $this->io->getOutput(),
        );
    }

    public function testAStepBeyondTheTotalIsCappedAtTheFullBar(): void
    {
        $this->bar->advanceTo(30, 20);

        $this->assertSame("[\033[32m##########\033[0m] 100% Complete" . PHP_EOL, $this->io->getOutput());
    }

    public function testFractionalStepsAreAccepted(): void
    {
        $this->bar->advanceTo(0, 10);
        $this->bar->advanceTo(2.5, 10);

        $this->assertSame(
            "[\033[32m..........\033[0m]   0% Complete" . PHP_EOL
            . "\033[1A[\033[32m###.......\033[0m]  25% Complete" . PHP_EOL,
            $this->io->getOutput(),
        );
    }

    public function testANegativeStepIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The step must be zero or more, -1 given.');

        $this->bar->advanceTo(-1, 10);
    }

    public function testATotalBelowOneIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The total steps must be one or more, 0 given.');

        $this->bar->advanceTo(1, 0);
    }

    public function testTheBarLeavesTheCursorAtTheLineStart(): void
    {
        $output = new Output($this->io);
        $bar    = new ProgressBar($output);

        $bar->advanceTo(1, 20);
        $output->write('done');

        $this->assertSame("[\033[32m#.........\033[0m]   5% Complete" . PHP_EOL . 'done' . PHP_EOL, $this->io->getOutput());
    }
}
