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
use CodeIgniter\CLI\CLI;
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
        'name' => 'The test class name.',
    ];

    /**
     * The Command's Options
     *
     * @var array<string, string>
     */
    protected $options = [
        '--namespace' => 'Set root namespace. Default: "Tests".',
        '--force'     => 'Force overwrite existing file.',
    ];

    /**
     * Actually execute a command.
     */
    public function run(array $params)
    {
        $this->component = 'Test';
        $this->template  = 'test.tpl.php';

        $this->classNameLang = 'CLI.generator.className.test';

        $autoload = service('autoloader');
        $autoload->addNamespace('CodeIgniter', TESTPATH . 'system');
        $autoload->addNamespace('Tests', ROOTPATH . 'tests');

        $this->generateClass($params);
    }

    /**
     * Gets the namespace from input or the default namespace.
     */
    protected function getNamespace(): string
    {
        if ($this->namespace !== null) {
            return $this->namespace;
        }

        if ($this->getOption('namespace') !== null) {
            return trim(
                str_replace(
                    '/',
                    '\\',
                    $this->getOption('namespace')
                ),
                '\\'
            );
        }

        $class      = $this->normalizeInputClassName();
        $classPaths = explode('\\', $class);

        $namespaces = service('autoloader')->getNamespace();

        while ($classPaths !== []) {
            array_pop($classPaths);
            $namespace = implode('\\', $classPaths);

            foreach (array_keys($namespaces) as $prefix) {
                if ($prefix === $namespace) {
                    // The input classname is FQCN, and use the namespace.
                    return $namespace;
                }
            }
        }

        return 'Tests';
    }

    /**
     * Builds the test file path from the class name.
     *
     * @param string $class namespaced classname.
     */
    protected function buildPath(string $class): string
    {
        $namespace = $this->getNamespace();

        $base = $this->searchTestFilePath($namespace);

        if ($base === null) {
            CLI::error(
                lang('CLI.namespaceNotDefined', [$namespace]),
                'light_gray',
                'red'
            );
            CLI::newLine();

            return '';
        }

        $realpath = realpath($base);
        $base     = ($realpath !== false) ? $realpath : $base;

        $file = $base . DIRECTORY_SEPARATOR
            . str_replace(
                '\\',
                DIRECTORY_SEPARATOR,
                trim(str_replace($namespace . '\\', '', $class), '\\')
            ) . '.php';

        return implode(
            DIRECTORY_SEPARATOR,
            array_slice(
                explode(DIRECTORY_SEPARATOR, $file),
                0,
                -1
            )
        ) . DIRECTORY_SEPARATOR . $this->basename($file);
    }

    /**
     * Returns test file path for the namespace.
     */
    private function searchTestFilePath(string $namespace): ?string
    {
        $bases = service('autoloader')->getNamespace($namespace);

        $base = null;

        foreach ($bases as $candidate) {
            if (str_contains($candidate, '/tests/')) {
                $base = $candidate;

                break;
            }
        }

        return $base;
    }
}
