<?php namespace CodeIgniter\CLI;

use Psr\Log\LoggerInterface;

class BaseCommand
{
    /**
     * List of aliases and the method
     * in this class they point to.
     * Allows for more flexible naming of
     * CLI commands.
     *
     * @var array
     */
    protected $tasks = [];

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    //--------------------------------------------------------------------

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    //--------------------------------------------------------------------

    /**
     * Checks to see if this command has the listed task.
     *
     * @param string $alias
     *
     * @return bool
     */
    public function hasTask(string $alias): bool
    {
        return array_key_exists(mb_strtolower($alias), $this->tasks);
    }

    //--------------------------------------------------------------------

    /**
     * Used by the commandRunner to actually execute the command by
     * it's alias. $params are passed to the command in the order
     * presented on the command line.
     *
     * @param string $alias
     * @param array  $params
     */
    public function runTask(string $alias, array $params)
    {
        if (! $this->hasTask($alias))
        {
            throw new \InvalidArgumentException('Invalid alias: '. $alias);
        }

        $method = $this->tasks[$alias];

        return $this->$method($params);
    }

    //--------------------------------------------------------------------
}
