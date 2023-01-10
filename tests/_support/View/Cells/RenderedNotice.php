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

class RenderedNotice extends Cell
{
    public string $message = '4, 8, 15, 16, 23, 42';

    public function render(): string
    {
        return $this->view('notice');
    }
}
