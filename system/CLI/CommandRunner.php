<?php namespace CodeIgniter\CLI;

use CodeIgniter\Controller;

class CommandRunner extends Controller
{
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

        $class = $this->locateCommand($command);

        return $class->runTask($command, $params);
    }

    //--------------------------------------------------------------------

    /**
     * Attempts to find a class matching our snake_case to UpperCamelCase
     * version of the command passed to us.
     *
     * @param string $command
     *
     * @return null
     */
    protected function locateCommand(string $command)
    {
        if (empty($command)) return;

        // Convert the command to UpperCamelCase
        $command = str_replace(' ', '', ucwords(str_replace('_', ' ', $command)));

        $files = service('locator')->search("Commands/{$command}");

        // If no matching command files were found, bail
        if (empty($files))
        {
            return null;
        }

        // Loop over each file checking to see if a command with that
        // alias exists in the class. If so, return it. Otherwise, try the next.
        foreach ($files as $file)
        {
            $class = service('locator')->findQualifiedNameFromPath($file);

            if (empty($class))
            {
                continue;
            }

            $class = new $class($this->logger);

            if ($class->hasTask($command))
            {
                return $class;
            }

            $class = null;
            unset($class);
        }
    }

    //--------------------------------------------------------------------
}
