<?php namespace CodeIgniter\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

/**
 * Does a rollback followed by a latest to refresh the current state
 * of the database.
 *
 * @package CodeIgniter\Commands
 */
class MigrateRefresh extends BaseCommand
{
    protected $group = 'Database';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'migrate:refresh';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Does a rollback followed by a latest to refresh the current state of the database.';

    /**
     * Does a rollback followed by a latest to refresh the current state
     * of the database.
     */
    public function run(array $params=[])
    {
        $this->call('migrate:rollback');
        $this->call('migrate:latest');
    }
}
