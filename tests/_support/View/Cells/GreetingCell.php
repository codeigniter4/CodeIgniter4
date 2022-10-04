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

class GreetingCell extends Cell
{
    public string $greeting = 'Hello';
    public string $name     = 'World';

    public function sayHello(): string
    {
        return 'Well, ' . $this->greeting . ' ' . $this->name;
    }
}
