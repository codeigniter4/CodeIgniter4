<?php namespace CodeIgniter\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

/**
 * Creates a new migration file.
 *
 * @package CodeIgniter\Commands
 */
class MigrateLatest extends BaseCommand
{
    protected $group = 'Database';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'migrate';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Migrates the database to the latest schema.';

    /**
     * Ensures that all migrations have been run.
     */
    public function run(array $params=[])
    {
        $runner = Services::migrations();

        CLI::write(lang('Migrations.migToLatest'), 'yellow');

        try {
            $runner->latest();
        }
        catch (\Exception $e)
        {
            $this->showError($e);
        }

        CLI::write('Done');
    }
}
