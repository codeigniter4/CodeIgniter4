<?php namespace CodeIgniter\Database;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\ConfigException;

class Seeder
{
	protected $seedPath;

	protected $config;

	//--------------------------------------------------------------------

	public function __construct(BaseConfig $config)
	{
	    $this->seedPath = $config->filesPath ?? APPPATH.'Database/';

		if (empty($this->seedPath))
		{
			throw new \InvalidArgumentException('Invalid filesPath set in the Config\Database.');
		}

		$this->seedPath = rtrim($this->seedPath, '/').'/Seeds/';

		if (! is_dir($this->seedPath))
		{
			throw new \InvalidArgumentException('Unable to locate the seeds directory. Please check Config\Database::filesPath');
		}

		$this->config =& $config;
	}

	//--------------------------------------------------------------------

	/**
	 * Loads the specified seeder and runs it.
	 *
	 * @param string $class
	 *
	 * @throws RuntimeException
	 */
	public function call(string $class)
	{
	    if (empty($class))
	    {
		    // Ask the user...
		    $class = trim(CLI::prompt("Seeder name"));

			if (empty($class))
			{
				throw new \InvalidArgumentException('No Seeder was specified.');
			}
	    }
		
		$path = $this->seedPath.str_replace('.php', '', $class).'.php';
		
		if (! is_file($path))
		{
			throw new RuntimeException('The specified Seeder is not a valid file: '. $path);
		}

		try
		{
			require $path;

			$seeder = new $class($this->config);

			$seeder->run();

			unset($seeder);
		}
		catch (\Exception $e)
		{
			CLI::error($e->getMessage());
			CLI::write($e->getFile().' - '.$e->getLine(), 'white');
		}

		CLI::write("Seeded: {$class}", 'green');
	}

	//--------------------------------------------------------------------


	/**
	 * Run the database seeds. This is where the magic happends.
	 *
	 * Child classes must implement this method and take care
	 * of inserting their data here.
	 *
	 * @return mixed
	 */
	public function run()
	{

	}

	//--------------------------------------------------------------------

}
