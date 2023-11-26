<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\View\Cells;

use CodeIgniter\View\Cells\Cell;

class MultiplierCell extends Cell
{
    public int $value      = 2;
    public int $multiplier = 2;

    public function mount(): void
    {
        $this->value *= $this->multiplier;
    }
}
