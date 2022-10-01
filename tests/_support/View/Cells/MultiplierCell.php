<?php

namespace Tests\Support\View\Cells;

use CodeIgniter\View\Cells\Cell;

class MultiplierCell extends Cell
{
    public int $value = 2;
    public int $multiplier = 2;

    public function mount(): void
    {
        $this->value = $this->value * $this->multiplier;
    }
}
