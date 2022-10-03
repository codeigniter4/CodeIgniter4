<?php

namespace Tests\Support\View\Cells;

use CodeIgniter\View\Cells\Cell;

class GreetingCell extends Cell
{
    public string $greeting = 'Hello';
    public string $name = 'World';

    public function sayHello(): string
    {
        return 'Well, '. $this->greeting .' '. $this->name;
    }
}
