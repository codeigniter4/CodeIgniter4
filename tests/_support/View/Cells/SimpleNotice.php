<?php

namespace Tests\Support\View\Cells;

use CodeIgniter\View\Cells\Cell;

class SimpleNotice extends Cell
{
    protected string $view = 'notice';

    public string $message = '4, 8, 15, 16, 23, 42';
}
