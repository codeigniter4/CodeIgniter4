<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractGeneratorCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Attributes\GeneratorCommand;
use CodeIgniter\CLI\Input\Option;

#[Command(name: 'make:widget', description: 'Generates a new widget class.', group: 'Generators')]
#[GeneratorCommand(component: 'Widget', template: 'widget.tpl.php', directory: 'Widgets')]
class WidgetGenerator extends AbstractGeneratorCommand
{
    protected function configure(): void
    {
        // Registers the required "name" argument.
        parent::configure();

        $this->addOption(new Option(
            name: 'table',
            description: 'Table name to use.',
            requiresValue: true,
            default: 'widgets',
        ));
    }

    protected function getReplacements(string $class): array
    {
        // Replaces the {table} placeholder in the template.
        return ['{table}' => (string) $this->getValidatedOption('table')];
    }

    protected function getTemplateData(string $class): array
    {
        // Available as a plain $sortable variable inside the template.
        return ['sortable' => true];
    }
}
