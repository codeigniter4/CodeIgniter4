<?php namespace CodeIgniter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * CI Help command for the ci.php script.
 *
 * Lists the basic usage information for the ci.php script,
 * and provides a way to list help for other commands.
 *
 * @package CodeIgniter\Commands
 */
class Help extends BaseCommand
{
    protected $group = 'CodeIgniter';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'help';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Displays basic usage information.';

    //--------------------------------------------------------------------

    /**
     * Displays the help for the ci.php cli script itself.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        CLI::write('Usage:');
        CLI::write("\tcommand [arguments]");

        $this->call('list');

        CLI::newLine();
    }
}
