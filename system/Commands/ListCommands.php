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
class ListCommands extends BaseCommand
{
    protected $group = 'CodeIgniter';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'list';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Lists the available commands.';

    /**
     * The length of the longest command name.
     * Used during display in columns.
     *
     * @var int
     */
    protected $maxFirstLength = 0;

    //--------------------------------------------------------------------

    /**
     * Displays the help for the ci.php cli script itself.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $commands = $this->commands->getCommands();

        $this->describeCommands($commands);

        CLI::newLine();
    }

    //--------------------------------------------------------------------

    /**
     * Displays the commands on the CLI.
     *
     * @param array $commands
     */
    protected function describeCommands(array $commands = [])
    {
        arsort($commands);

        $names     = array_keys($commands);
        $descs     = array_column($commands, 'description');
        $groups    = array_column($commands, 'group');
        $lastGroup = '';

        // Pad each item to the same length
        $names = $this->padArray($names, 2, 2);

        for ($i = 0; $i < count($names); $i++)
        {
            $lastGroup = $this->describeGroup($groups[$i], $lastGroup);

            $out = CLI::color($names[$i], 'yellow');

            if (isset($descs[$i]))
            {
                $out .= CLI::wrap($descs[$i], 125, strlen($names[$i]));
            }

            CLI::write($out);
        }
    }

    //--------------------------------------------------------------------

    /**
     * Outputs the description, if necessary.
     *
     * @param string $new
     * @param string $old
     *
     * @return string
     */
    protected function describeGroup(string $new, string $old)
    {
        if ($new == $old)
        {
            return $old;
        }

        CLI::newLine();
        CLI::write($new);

        return $new;
    }

    //--------------------------------------------------------------------

    /**
     * Returns a new array where all of the string elements have
     * been padding with trailing spaces to be the same length.
     *
     * @param array $array
     * @param int   $extra // How many extra spaces to add at the end
     *
     * @return array
     */
    protected function padArray($array, $extra = 2, $indent=0)
    {
        $max = max(array_map('strlen', $array))+$extra+$indent;

        foreach ($array as &$item)
        {
            $item = str_repeat(' ', $indent).$item;
            $item = str_pad($item, $max);
        }

        return $array;
    }

    //--------------------------------------------------------------------

}
