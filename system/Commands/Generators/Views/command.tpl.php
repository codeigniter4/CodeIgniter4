<@php

namespace {namespace};

<?php if ($type === 'generator'): ?>
use CodeIgniter\CLI\AbstractGeneratorCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Attributes\GeneratorCommand;

#[Command(name: '{command}', description: '', group: '{group}')]
#[GeneratorCommand(component: 'Command', template: 'command.tpl.php', directory: 'Commands')]
class {class} extends AbstractGeneratorCommand
{
}
<?php else: ?>
use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;

#[Command(name: '{command}', description: '', group: '{group}')]
class {class} extends AbstractCommand
{
    protected function execute(array $arguments, array $options): int
    {
        //

        return EXIT_SUCCESS;
    }
}
<?php endif ?>
