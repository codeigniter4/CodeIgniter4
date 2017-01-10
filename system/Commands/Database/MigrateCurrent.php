<?php namespace CodeIgniter\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

/**
 * Migrates the DB to version set in config file, $currentVersion.
 *
 * @package CodeIgniter\Commands
 */
class MigrateCurrent extends BaseCommand
{
    protected $group = 'Database';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'migrate:current';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Migrates us up or down to the version specified as $currentVersion in the migrations config file.';

    /**
     * Migrates us up or down to the version specified as $currentVersion
     * in the migrations config file.
     */
    public function run(array $params=[])
    {
        $runner = Services::migrations();

        CLI::write(lang('Migrations.migToVersion'), 'yellow');

        try {
            $runner->current();
        }
        catch (\Exception $e)
        {
            $this->showError($e);
        }

        CLI::write('Done');
    }
}
