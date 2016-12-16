<?php namespace CodeIgniter\CLI;

use Psr\Log\LoggerInterface;

/**
 * Class BaseCommand
 *
 * @property $group
 * @property $name
 * @property $description
 *
 * @package CodeIgniter\CLI
 */
abstract class BaseCommand
{
    /**
     * The group the command is lumped under
     * when listing commands.
     *
     * @var string
     */
    protected $group;

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name;

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Instance of the CommandRunner controller
     * so commands can call other commands.
     *
     * @var \CodeIgniter\CLI\CommandRunner
     */
    protected $commands;

    //--------------------------------------------------------------------

    public function __construct(LoggerInterface $logger, CommandRunner $commands)
    {
        $this->logger = $logger;
        $this->commands = $commands;
    }

    //--------------------------------------------------------------------

    abstract public function run(array $params);

    //--------------------------------------------------------------------

    /**
     * Can be used by a command to run other commands.
     *
     * @param string $command
     * @param array  $params
     */
    protected function call(string $command, array $params=[])
    {
        // The CommandRunner will grab the first element
        // for the command name.
        array_unshift($params, $command);

        return $this->commands->index($params);
    }

    //--------------------------------------------------------------------

    public function __get(string $key)
    {
        if (isset($this->$key))
        {
            return $this->$key;
        }
    }

    //--------------------------------------------------------------------
}
