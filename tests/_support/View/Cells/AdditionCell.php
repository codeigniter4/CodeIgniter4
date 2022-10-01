<?php

namespace Tests\Support\View\Cells;

use CodeIgniter\View\Cells\Cell;

class AdditionCell extends Cell
{
    public int $value = 2;

    public function mount(int $number=null, bool $skipAddition = false)
    {
        $this->value = ! $skipAddition
            ? $this->value + (int)$number
            : $this->value;
    }
}
