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
use CodeIgniter\CLI\GeneratorTrait;

/**
 * Generates a skeleton Filter file.
 */
class FilterGenerator extends BaseCommand
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
    protected $name = 'make:filter';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Generates a new filter file.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'make:filter <name> [options]';

    /**
     * The Command's Arguments
     *
     * @var array<string, string>
     */
    protected $arguments = [
        'name' => 'The filter class name.',
    ];

    /**
     * The Command's Options
     *
     * @var array<string, string>
     */
    protected $options = [
        '--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
        '--suffix'    => 'Append the component title to the class name (e.g. User => UserFilter).',
        '--force'     => 'Force overwrite existing file.',
    ];

    /**
     * Actually execute a command.
     */
    public function run(array $params)
    {
        $this->component = 'Filter';
        $this->directory = 'Filters';
        $this->template  = 'filter.tpl.php';

        $this->classNameLang = 'CLI.generator.className.filter';
        $this->execute($params);
    }
}
