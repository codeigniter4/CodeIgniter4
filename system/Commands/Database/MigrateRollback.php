<?php namespace CodeIgniter\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

/**
 * Runs all of the migrations in reverse order, until they have
 * all been un-applied.
 *
 * @package CodeIgniter\Commands
 */
class MigrateRollback extends BaseCommand
{
    protected $group = 'Database';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'migrate:rollback';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Runs all of the migrations in reverse order, until they have all been un-applied.';

    /**
     * Runs all of the migrations in reverse order, until they have
     * all been un-applied.
     */
    public function run(array $params=[])
    {
        $runner = Services::migrations();

        CLI::write(lang('Migrations.migRollingBack'), 'yellow');

        try {
            $runner->version(0);
        }
        catch (\Exception $e)
        {
            $this->showError($e);
        }

        CLI::write('Done');
    }
}
