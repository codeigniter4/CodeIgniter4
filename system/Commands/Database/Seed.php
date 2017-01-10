<?php namespace CodeIgniter\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Seeder;
use Config\Services;

/**
 * Runs the specified Seeder file to populate the database
 * with some data.
 *
 * @package CodeIgniter\Commands
 */
class Seed extends BaseCommand
{
    protected $group = 'Database';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'db:seed';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Runs the specified seeder to populate known data into the database.';

    /**
     * Runs all of the migrations in reverse order, until they have
     * all been un-applied.
     */
    public function run(array $params=[])
    {
        $seeder = new Seeder(new \Config\Database());

        $seedName = array_shift($params);

        if (empty($seedName))
        {
            $seedName = CLI::prompt(lang('Migrations.migSeeder'), 'DatabaseSeeder');
        }

        if (empty($seedName))
        {
            CLI::error(lang('Migrations.migMissingSeeder'));
            return;
        }

        try
        {
            $seeder->call($seedName);
        }
        catch (\Exception $e)
        {
            $this->showError($e);
        }
    }
}
