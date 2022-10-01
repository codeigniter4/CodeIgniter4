<?php

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
