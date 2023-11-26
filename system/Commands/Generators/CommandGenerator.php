<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Generators;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\GeneratorTrait;

/**
 * Generates a skeleton command file.
 */
class CommandGenerator extends BaseCommand
{
    use GeneratorTrait;

    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Generators';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'make:command';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Generates a new spark command.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'make:command <name> [options]';

    /**
     * The Command's Arguments
     *
     * @var array<string, string>
     */
    protected $arguments = [
        'name' => 'The command class name.',
    ];

    /**
     * The Command's Options
     *
     * @var array<string, string>
     */
    protected $options = [
        '--command'   => 'The command name. Default: "command:name"',
        '--type'      => 'The command type. Options [basic, generator]. Default: "basic".',
        '--group'     => 'The command group. Default: [basic -> "App", generator -> "Generators"].',
        '--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
        '--suffix'    => 'Append the component title to the class name (e.g. User => UserCommand).',
        '--force'     => 'Force overwrite existing file.',
    ];

    /**
     * Actually execute a command.
     */
    public function run(array $params)
    {
        $this->component = 'Command';
        $this->directory = 'Commands';
        $this->template  = 'command.tpl.php';

        $this->classNameLang = 'CLI.generator.className.command';
        $this->execute($params);
    }

    /**
     * Prepare options and do the necessary replacements.
     */
    protected function prepare(string $class): string
    {
        $command = $this->getOption('command');
        $group   = $this->getOption('group');
        $type    = $this->getOption('type');

        $command = is_string($command) ? $command : 'command:name';
        $type    = is_string($type) ? $type : 'basic';

        if (! in_array($type, ['basic', 'generator'], true)) {
            // @codeCoverageIgnoreStart
            $type = CLI::prompt(lang('CLI.generator.commandType'), ['basic', 'generator'], 'required');
            CLI::newLine();
            // @codeCoverageIgnoreEnd
        }

        if (! is_string($group)) {
            $group = $type === 'generator' ? 'Generators' : 'App';
        }

        return $this->parseTemplate(
            $class,
            ['{group}', '{command}'],
            [$group, $command],
            ['type' => $type]
        );
    }
}
