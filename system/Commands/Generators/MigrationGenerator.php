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
use Config\Database;
use Config\Migrations;
use Config\Session as SessionConfig;

/**
 * Generates a skeleton migration file.
 */
class MigrationGenerator extends BaseCommand
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
    protected $name = 'make:migration';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Generates a new migration file.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'make:migration <name> [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'name' => 'The migration class name.',
    ];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--session'   => 'Generates the migration file for database sessions.',
        '--table'     => 'Table name to use for database sessions. Default: "ci_sessions".',
        '--dbgroup'   => 'Database group to use for database sessions. Default: "default".',
        '--namespace' => 'Set root namespace. Default: "APP_NAMESPACE".',
        '--suffix'    => 'Append the component title to the class name (e.g. User => UserMigration).',
    ];

    /**
     * Actually execute a command.
     */
    public function run(array $params)
    {
        $this->component = 'Migration';
        $this->directory = 'Database\Migrations';
        $this->template  = 'migration.tpl.php';

        if (array_key_exists('session', $params) || CLI::getOption('session')) {
            $table     = $params['table'] ?? CLI::getOption('table') ?? 'ci_sessions';
            $params[0] = "_create_{$table}_table";
        }

        $this->classNameLang = 'CLI.generator.className.migration';
        $this->execute($params);
    }

    /**
     * Prepare options and do the necessary replacements.
     */
    protected function prepare(string $class): string
    {
        $data            = [];
        $data['session'] = false;

        if ($this->getOption('session')) {
            $table   = $this->getOption('table');
            $DBGroup = $this->getOption('dbgroup');

            $data['session']  = true;
            $data['table']    = is_string($table) ? $table : 'ci_sessions';
            $data['DBGroup']  = is_string($DBGroup) ? $DBGroup : 'default';
            $data['DBDriver'] = config(Database::class)->{$data['DBGroup']}['DBDriver'];

            /** @var SessionConfig|null $session */
            $session = config(SessionConfig::class);

            $data['matchIP'] = $session->matchIP;
        }

        return $this->parseTemplate($class, [], [], $data);
    }

    /**
     * Change file basename before saving.
     */
    protected function basename(string $filename): string
    {
        return gmdate(config(Migrations::class)->timestampFormat) . basename($filename);
    }
}
