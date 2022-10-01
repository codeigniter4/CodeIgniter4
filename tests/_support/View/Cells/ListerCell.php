<?php

namespace Tests\Support\View\Cells;

use CodeIgniter\View\Cells\Cell;

class ListerCell extends Cell
{
    protected array $items = [];

    public function getItemsProperty()
    {
        $items = array_map(function($item) {
            return $item = '-'. $item;
        }, $this->items);

        return $items;
    }
}
