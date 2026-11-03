<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\GeneratorTrait;

class WidgetGenerator extends BaseCommand
{
    use GeneratorTrait;

    protected $group       = 'Generators';
    protected $name        = 'make:widget';
    protected $description = 'Generates a new widget class.';
    protected $usage       = 'make:widget <name> [options]';
    protected $arguments   = ['name' => 'The widget class name.'];
    protected $options     = [
        '--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
        '--suffix'    => 'Append the component title to the class name.',
        '--force'     => 'Force overwrite existing file.',
    ];

    public function run(array $params)
    {
        $this->component = 'Widget';
        $this->directory = 'Widgets';
        $this->template  = 'widget.tpl.php';

        $this->generateClass($params);

        return EXIT_SUCCESS;
    }
}
