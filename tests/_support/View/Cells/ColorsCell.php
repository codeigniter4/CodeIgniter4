<?php

namespace Tests\Support\View\Cells;

use CodeIgniter\View\Cells\Cell;

class ColorsCell extends Cell
{
    public string $color = '';

    public function colorType(): string
    {
        $warmColors = ['red', 'orange', 'yellow'];
        $coolColors = ['green', 'blue', 'purple'];

        if (in_array($this->color, $warmColors)) {
            return 'warm';
        }

        if (in_array($this->color, $coolColors)) {
            return 'cool';
        }

        return 'unknown';
    }
}
