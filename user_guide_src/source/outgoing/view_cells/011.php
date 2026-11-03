<?php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;

class AlertMessageCell extends Cell
{
    public $type;
    public $message;

    protected string $view = APPPATH . 'Views/cells/alerts.php';
}
