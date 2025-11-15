<?php

declare(strict_types=1);

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
use Config\Generators;

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
    protected $description = 'Generates a new Controlled Cell file and its view.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'make:cell <name> [options]';

    /**
     * The Command's Arguments
     *
     * @var array<string, string>
     */
    protected $arguments = [
        'name' => 'The Controlled Cell class name.',
    ];

    /**
     * The Command's Options
     *
     * @var array<string, string>
     */
    protected $options = [
        '--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
        '--force'     => 'Force overwrite existing file.',
    ];

    /**
     * Actually execute a command.
     */
    public function run(array $params)
    {
        $this->component = 'Cell';
        $this->directory = 'Cells';

        $params = array_merge($params, ['suffix' => null]);

        $this->templatePath  = config(Generators::class)->views[$this->name]['class'];
        $this->template      = 'cell.tpl.php';
        $this->classNameLang = 'CLI.generator.className.cell';

        $this->generateClass($params);

        $this->templatePath  = config(Generators::class)->views[$this->name]['view'];
        $this->template      = 'cell_view.tpl.php';
        $this->classNameLang = 'CLI.generator.viewName.cell';

        $className = $this->qualifyClassName();
        $viewName  = decamelize(class_basename($className));
        $viewName  = preg_replace(
            '/([a-z][a-z0-9_\/\\\\]+)(_cell)$/i',
            '$1',
            $viewName,
        ) ?? $viewName;
        $namespace = substr($className, 0, strrpos($className, '\\') + 1);

        $this->generateView($namespace . $viewName, $params);

        return 0;
    }
}
