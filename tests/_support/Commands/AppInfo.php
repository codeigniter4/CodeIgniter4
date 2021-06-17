<?php

namespace Tests\Support\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CodeIgniter;

class AppInfo extends BaseCommand
{
    protected $group       = 'demo';
    protected $name        = 'app:info';
    protected $arguments   = ['draft' => 'unused'];
    protected $description = 'Displays basic application information.';

    public function run(array $params)
    {
        CLI::write('CI Version: ' . CLI::color(CodeIgniter::CI_VERSION, 'red'));
    }

    public function bomb()
    {
        try {
            CLI::color('test', 'white', 'Background');
        } catch (\RuntimeException $oops) {
            $this->showError($oops);
        }
    }

    public function helpme()
    {
        $this->call('help');
    }
}
