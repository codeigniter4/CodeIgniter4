<?php

class ConfigCollection extends \CodeIgniter\Files\FileCollection
{
    protected function define(): void
    {
        $this->add(APPPATH . 'Config', true)->retainPattern('*.php');
    }
}
