<?php namespace CodeIgniter\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

/**
 * Displays a list of all migrations and whether they've been run or not.
 *
 * @package CodeIgniter\Commands
 */
class MigrateStatus extends BaseCommand
{
    protected $group = 'Database';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'migrate:status';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Displays a list of all migrations and whether they\'ve been run or not.';

    /**
     * Displays a list of all migrations and whether they've been run or not.
     */
    public function run(array $params=[])
    {
        $runner = Services::migrations();

        $migrations = $runner->findMigrations();
        $history    = $runner->getHistory();

        if (empty($migrations))
        {
            return CLI::error(lang('Migrations.migNoneFound'));
        }

        $max = 0;

        foreach ($migrations as $version => $file)
        {
            $file = substr($file, strpos($file, $version.'_'));
            $migrations[$version] = $file;

            $max = max($max, strlen($file));
        }

        CLI::write(str_pad(lang('Migrations.filename'), $max+4).lang('Migrations.migOn'), 'yellow');

        foreach ($migrations as $version => $file)
        {
            $date = '';
            foreach ($history as $row)
            {
                if ($row['version'] != $version) continue;

                $date = $row['time'];
            }

            CLI::write(str_pad($file, $max+4). ($date ? $date : '---'));
        }
    }
}
