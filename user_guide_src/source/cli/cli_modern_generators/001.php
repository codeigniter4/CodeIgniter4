<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractGeneratorCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Attributes\GeneratorCommand;

#[Command(name: 'make:widget', description: 'Generates a new widget class.', group: 'Generators')]
#[GeneratorCommand(component: 'Widget', template: 'widget.tpl.php', directory: 'Widgets')]
class WidgetGenerator extends AbstractGeneratorCommand
{
}
