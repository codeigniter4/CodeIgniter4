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

class ColorsCell extends Cell
{
    public string $color = '';

    public function colorType(): string
    {
        $warmColors = ['red', 'orange', 'yellow'];
        $coolColors = ['green', 'blue', 'purple'];

        if (in_array($this->color, $warmColors, true)) {
            return 'warm';
        }

        if (in_array($this->color, $coolColors, true)) {
            return 'cool';
        }

        return 'unknown';
    }
}
