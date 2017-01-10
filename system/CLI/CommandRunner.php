<?php namespace CodeIgniter\CLI;

use CodeIgniter\Controller;

class CommandRunner extends Controller
{
    /**
     * Stores the info about found Commands.
     *
     * @var array
     */
    protected $commands = [];

    //--------------------------------------------------------------------

    /**
     * We map all un-routed CLI methods through this function
     * so we have the chance to look for a Command first.
     *
     * @param       $method
     * @param array ...$params
     */
    public function _remap($method, ...$params)
    {
        // The first param is usually empty, so scrap it.
        if (empty($params[0]))
        {
            array_shift($params);
        }

        $this->index($params);
    }

    //--------------------------------------------------------------------

    public function index(array $params)
    {
        $command = array_shift($params);

        $this->createCommandList($command);

        if (is_null($command))
        {
            $command = 'help';
        }

        return $this->runCommand($command, $params);
    }

    //--------------------------------------------------------------------

    /**
     * Actually runs the command.
     *
     * @param string $command
     */
    protected function runCommand(string $command, array $params)
    {
        if (! isset($this->commands[$command]))
        {
            CLI::error('Command \''.$command.'\' not found');
            CLI::newLine();
            return;
        }

        // The file would have already been loaded during the
        // createCommandList function...
        $className = $this->commands[$command]['class'];
        $class = new $className($this->logger, $this);

        return $class->run($params);
    }

    //--------------------------------------------------------------------

    /**
     * Scans all Commands directories and prepares a list
     * of each command with it's group and file.
     *
     * @return null|void
     */
    protected function createCommandList()
    {
        $files = service('locator')->listFiles("Commands/");

        // If no matching command files were found, bail
        if (empty($files))
        {
            return;
        }

        // Loop over each file checking to see if a command with that
        // alias exists in the class. If so, return it. Otherwise, try the next.
        foreach ($files as $file)
        {
            $className = service('locator')->findQualifiedNameFromPath($file);

            if (empty($className) || ! class_exists($className))
            {
                continue;
            }

            $class = new $className($this->logger, $this);

            // Store it!
            if ($class->group !== null)
            {
                $this->commands[$class->name] = [
                    'class' => $className,
                    'file' => $file,
                    'group' => $class->group,
                    'description' => $class->description
                ];
            }

            $class = null;
            unset($class);
        }

        asort($this->commands);
    }

    //--------------------------------------------------------------------

    /**
     * Allows access to the current commands that have been found.
     *
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
    }

    //--------------------------------------------------------------------
}
