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
use Config\App;
use Config\Migrations;

/**
 * Generates a migration file for database sessions.
 *
 * @deprecated Use `make:migration --session` instead.
 *
 * @codeCoverageIgnore
 */
class SessionMigrationGenerator extends BaseCommand
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
    protected $name = 'session:migration';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = '[DEPRECATED] Generates the migration file for database sessions, Please use  "make:migration --session" instead.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'session:migration [options]';

    /**
     * The Command's Options
     *
     * @var array<string, string>
     */
    protected $options = [
        '-t' => 'Supply a table name.',
        '-g' => 'Database group to use. Default: "default".',
    ];

    /**
     * Actually execute a command.
     */
    public function run(array $params)
    {
        $this->component = 'Migration';
        $this->directory = 'Database\Migrations';
        $this->template  = 'migration.tpl.php';

        $table = 'ci_sessions';

        if (array_key_exists('t', $params) || CLI::getOption('t')) {
            $table = $params['t'] ?? CLI::getOption('t');
        }

        $params[0] = "_create_{$table}_table";

        $this->execute($params);
    }

    /**
     * Performs the necessary replacements.
     */
    protected function prepare(string $class): string
    {
        $data            = [];
        $data['session'] = true;
        $data['table']   = $this->getOption('t');
        $data['DBGroup'] = $this->getOption('g');
        $data['matchIP'] = config(App::class)->sessionMatchIP ?? false;

        $data['table']   = is_string($data['table']) ? $data['table'] : 'ci_sessions';
        $data['DBGroup'] = is_string($data['DBGroup']) ? $data['DBGroup'] : 'default';

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
