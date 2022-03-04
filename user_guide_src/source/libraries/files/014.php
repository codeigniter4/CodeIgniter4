<?php

use CodeIgniter\Files\FileCollection;

class ConfigCollection extends FileCollection
{
    protected function define(): void
    {
        $this->add(APPPATH . 'Config', true)->retainPattern('*.php');
    }
}
