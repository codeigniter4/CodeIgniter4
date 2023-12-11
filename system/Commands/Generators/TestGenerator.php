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

/**
 * Generates a skeleton command file.
 */
class TestGenerator extends BaseCommand
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
    protected $name = 'make:test';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Generates a new test file.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'make:test <name> [options]';

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
        '--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
        '--force'     => 'Force overwrite existing file.',
    ];

    /**
     * Actually execute a command.
     */
    public function run(array $params)
    {
        $this->component = 'Test';
        $this->template  = 'test.tpl.php';
        $this->namespace = 'Tests';

        $this->classNameLang = 'CLI.generator.className.entity';
        $this->generateClass($params);
    }
}
