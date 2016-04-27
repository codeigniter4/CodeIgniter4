<?php namespace CodeIgniter\Database;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\BaseConfig;

class Seeder
{
	/**
	 * The name of the database group to use.
	 * @var string
	 */
	protected $DBGroup = 'default';

	/**
	 * Where we can find the Seed files.
	 * @var string
	 */
	protected $seedPath;

	/**
	 * An instance of the main Database configuration
	 * @var BaseConfig
	 */
	protected $config;

	/**
	 * Database Connection instance
	 * @var BaseConnection
	 */
	protected $db;

	/**
	 * Database Forge instance.
	 * @var Forge
	 */
	protected $forge;

	//--------------------------------------------------------------------

	/**
	 * Seeder constructor.
	 *
	 * @param BaseConfig $config
	 * @param Forge|null $forge
	 */
	public function __construct(BaseConfig $config, Forge $forge = null)
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

		$this->forge = ! is_null($forge)
			? $forge
			: \Config\Database::forge($this->DBGroup);

		$this->db = $this->forge->getConnection();
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
			throw new \InvalidArgumentException('No Seeder was specified.');
	    }
		
		$path = $this->seedPath.str_replace('.php', '', $class).'.php';
		
		if (! is_file($path))
		{
			throw new \InvalidArgumentException('The specified Seeder is not a valid file: '. $path);
		}

		require $path;

		$seeder = new $class($this->config);

		$seeder->run();

		unset($seeder);

		if (is_cli())
		{
			CLI::write("Seeded: {$class}", 'green');
		}
	}

	//--------------------------------------------------------------------


	/**
	 * Run the database seeds. This is where the magic happens.
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
