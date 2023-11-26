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

class AdditionCell extends Cell
{
    public int $value = 2;

    public function mount(?int $number = null, bool $skipAddition = false): void
    {
        $this->value = ! $skipAddition
            ? $this->value + (int) $number
            : $this->value;
    }
}
