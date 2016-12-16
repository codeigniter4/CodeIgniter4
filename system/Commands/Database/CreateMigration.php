<?php namespace CodeIgniter\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Creates a new migration file.
 *
 * @package CodeIgniter\Commands
 */
class CreateMigration extends BaseCommand
{
    protected $group = 'Database';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'migrate:create';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Creates a new migration file.';

    /**
     * Displays the help for the ci.php cli script itself.
     */
    public function run(array $params)
    {
        CLI::write('Usage:');
        CLI::write("\tcommand [arguments]");
    }
}
