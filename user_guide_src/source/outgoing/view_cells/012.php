<?php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;

class AlertMessageCell extends Cell
{
    public $type;
    public $message;

    public function render(): string
    {
        return $this->view('my/custom/view', ['extra' => 'data']);
    }
}
