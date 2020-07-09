<?php namespace CodeIgniter\CLI;

use CodeIgniter\Log\Logger;
use Config\Services;

/**
 * Class Commands
 *
 * Core functionality for running, listing, etc commands.
 *
 * @package CodeIgniter\CLI
 */
class Commands
{
	/**
	 * The found commands.
	 *
	 * @var array
	 */
	protected $commands = [];

	/**
	 * Logger instance.
	 *
	 * @var Logger
	 */
	protected $logger;

	public function __construct($logger = null)
	{
		$this->logger = $logger === null ? service('logger') : $logger;
	}

	/**
	 * Runs a command given
	 *
	 * @param string $command
	 * @param array  $params
	 */
	public function run(string $command, array $params)
	{
		$this->discoverCommands();

		if (! isset($this->commands[$command]))
		{
			CLI::error(lang('CLI.commandNotFound', [$command]));
			CLI::newLine();
			return;
		}

		// The file would have already been loaded during the
		// createCommandList function...
		$className = $this->commands[$command]['class'];
		$class     = new $className($this->logger, $this);

		return $class->run($params);
	}

	/**
	 * Provide access to the list of commands.
	 *
	 * @return array
	 */
	public function getCommands()
	{
		$this->discoverCommands();

		return $this->commands;
	}

	/**
	 * Discovers all commands in the framework and within user code,
	 * and collects instances of them to work with.
	 */
	public function discoverCommands()
	{
		if (! empty($this->commands))
		{
			return;
		}

		$files = service('locator')->listFiles('Commands/');

		// If no matching command files were found, bail
		if (empty($files))
		{
			// This should never happen in unit testing.
			// if it does, we have far bigger problems!
			// @codeCoverageIgnoreStart
			return;
			// @codeCoverageIgnoreEnd
		}

		// Loop over each file checking to see if a command with that
		// alias exists in the class. If so, return it. Otherwise, try the next.
		foreach ($files as $file)
		{
			$className = Services::locator()->findQualifiedNameFromPath($file);
			if (empty($className) || ! class_exists($className))
			{
				continue;
			}

			try
			{
				$class = new \ReflectionClass($className);

				if (! $class->isInstantiable() || ! $class->isSubclassOf(BaseCommand::class))
				{
					continue;
				}

				$class = new $className($this->logger, $this);

				// Store it!
				if ($class->group !== null)
				{
					$this->commands[$class->name] = [
						'class'       => $className,
						'file'        => $file,
						'group'       => $class->group,
						'description' => $class->description,
					];
				}

				$class = null;
				unset($class);
			}
			catch (\ReflectionException $e)
			{
				$this->logger->error($e->getMessage());
			}
		}

		asort($this->commands);
	}
}
