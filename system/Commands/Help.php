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
    protected $tasks = [
        'help' => 'index'
    ];

    /**
     * Displays the help for the ci.php cli script itself.
     */
    public function index()
    {
        CLI::write('Usage:');
        CLI::write("\tcommands [arguments]");
    }
}
