<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\CLI;

use CodeIgniter\Controller;
use Config\Services;
use ReflectionException;

/**
 * Command runner
 */
class CommandRunner extends Controller
{
    /**
     * Instance of class managing the collection of commands
     *
     * @var Commands
     */
    protected $commands;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->commands = Services::commands();
    }

    /**
     * We map all un-routed CLI methods through this function
     * so we have the chance to look for a Command first.
     *
     * @param string $method
     * @param array  ...$params
     *
     * @throws ReflectionException
     *
     * @return mixed
     */
    public function _remap($method, ...$params)
    {
        // The first param is usually empty, so scrap it.
        if (empty($params[0])) {
            array_shift($params);
        }

        return $this->index($params);
    }

    /**
     * Default command.
     *
     * @param array $params
     *
     * @throws ReflectionException
     *
     * @return mixed
     */
    public function index(array $params)
    {
        $command = array_shift($params) ?? 'list';

        return $this->commands->run($command, $params);
    }

    /**
     * Allows access to the current commands that have been found.
     *
     * @return array
     */
    public function getCommands(): array
    {
        return $this->commands->getCommands();
    }
}
