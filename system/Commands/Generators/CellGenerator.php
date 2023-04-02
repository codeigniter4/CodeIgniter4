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
 * Generates a skeleton Cell and its view.
 */
class CellGenerator extends BaseCommand
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
    protected $name = 'make:cell';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Generates a new Cell file and its view.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'make:cell <name> [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'name' => 'The cell class name.',
    ];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
        '--suffix'    => 'Append the component title to the class name (e.g. User => UserCell).',
        '--force'     => 'Force overwrite existing file.',
    ];

    /**
     * Actually execute a command.
     */
    public function run(array $params)
    {
        // Generate the Class first
        $this->component     = 'Cell';
        $this->directory     = 'Cells';
        $this->template      = 'cell.tpl.php';
        $this->classNameLang = 'CLI.generator.className.cell';

        $this->generateClass($params);

        // Generate the View
        $this->classNameLang = 'CLI.generator.viewName.cell';

        // Form the view name
        $segments = explode('\\', $this->qualifyClassName());

        $view       = array_pop($segments);
        $view       = decamelize($view);
        $segments[] = $view;
        $view       = implode('\\', $segments);

        $this->template = 'cell_view.tpl.php';

        $this->generateView($view, $params);
    }
}
