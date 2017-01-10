<?php namespace CodeIgniter\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

/**
 * Migrates the DB to a specific version.
 *
 * @package CodeIgniter\Commands
 */
class MigrateVersion extends BaseCommand
{
    protected $group = 'Database';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'migrate:version';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Migrates the database up or down to get to the specified version.';

    /**
     * Migrates the database up or down to get to the specified version.
     */
    public function run(array $params=[])
    {
        $runner = Services::migrations();

        // Get the version number
        $version = array_shift($params);

        if (is_null($version))
        {
            $version = CLI::prompt(lang('Migrations.version'));
        }

        if (is_null($version))
        {
            CLI::error(lang('Migrations.invalidVersion'));
            exit();
        }

        CLI::write(sprintf(lang('Migrations.migToVersionPH'), $version), 'yellow');

        try {
            $runner->version($version);
        }
        catch (\Exception $e)
        {
            $this->showError($e);
        }

        CLI::write('Done');
    }
}
