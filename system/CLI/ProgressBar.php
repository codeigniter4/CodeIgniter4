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

/**
 * Ten-cell progress bar redrawn in place on the console.
 */
final class ProgressBar
{
    private const CELLS = 10;

    /**
     * The step drawn last, or `null` when no bar is on screen.
     */
    private float|int|null $step = null;

    public function __construct(private readonly Output $output)
    {
    }

    /**
     * Draws the bar for the step, capped at 100%, over the previous bar when the step did not go backwards.
     *
     * @param float|int<0, max> $step
     *
     * @throws InvalidArgumentException
     */
    public function advanceTo(float|int $step, int $totalSteps = 10): void
    {
        if ($step < 0) {
            throw new InvalidArgumentException(sprintf('The step must be zero or more, %s given.', $step));
        }

        if ($totalSteps < 1) {
            throw new InvalidArgumentException(sprintf('The total steps must be one or more, %d given.', $totalSteps));
        }

        $redraw     = $this->step !== null && $this->step <= $step;
        $this->step = $step;

        $percent = min(100, (int) ($step / $totalSteps * 100));
        $filled  = (int) round($percent / 10);

        $this->output->print(sprintf(
            "%s[\033[32m%s%s\033[0m] %3d%% Complete%s",
            $redraw ? "\033[1A" : '',
            str_repeat('#', $filled),
            str_repeat('.', self::CELLS - $filled),
            $percent,
            PHP_EOL,
        ));
    }

    /**
     * Ends the bar with a bell, so the next `advanceTo()` draws a new one.
     */
    public function clear(): void
    {
        $this->output->beep();

        $this->step = null;
    }
}
