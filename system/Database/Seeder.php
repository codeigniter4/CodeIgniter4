<?php namespace CodeIgniter\Database;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\BaseConfig;

/**
 * Class Seeder
 */
class Seeder
{

	/**
	 * The name of the database group to use.
	 * @var string
	 */
	protected $DBGroup;

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

	/**
	 * If true, will not display CLI messages.
	 * @var bool
	 */
	protected $silent = false;

	//--------------------------------------------------------------------

	/**
	 * Seeder constructor.
	 *
	 * @param BaseConfig $config
	 * @param BaseConnection $db
	 */
	public function __construct(BaseConfig $config, BaseConnection $db = null)
	{
		$this->seedPath = $config->filesPath ?? APPPATH . 'Database/';

		if (empty($this->seedPath))
		{
			throw new \InvalidArgumentException('Invalid filesPath set in the Config\Database.');
		}

		$this->seedPath = rtrim($this->seedPath, '/') . '/Seeds/';

		if ( ! is_dir($this->seedPath))
		{
			throw new \InvalidArgumentException('Unable to locate the seeds directory. Please check Config\Database::filesPath');
		}

		$this->config = & $config;

		if (is_null($db))
		{
			$db = \Config\Database::connect($this->DBGroup);
		}

		$this->db = & $db;
	}

	//--------------------------------------------------------------------

	/**
	 * Loads the specified seeder and runs it.
	 *
	 * @param string $class
	 *
	 * @throws \InvalidArgumentException
	 */
	public function call(string $class)
	{
		if (empty($class))
		{
			throw new \InvalidArgumentException('No Seeder was specified.');
		}

		$path = str_replace('.php', '', $class) . '.php';

		// If we have namespaced class, simply try to load it.
		if (strpos($class, '\\') !== false)
		{
			$seeder = new $class($this->config);
		}
		// Otherwise, try to load the class manually.
		else
		{
			$path = $this->seedPath . $path;

			if ( ! is_file($path))
			{
				throw new \InvalidArgumentException('The specified Seeder is not a valid file: ' . $path);
			}

			if ( ! class_exists($class, false))
			{
				require_once $path;
			}

			$seeder = new $class($this->config);
		}

		$seeder->run();

		unset($seeder);

		if (is_cli() && ! $this->silent)
		{
			CLI::write("Seeded: {$class}", 'green');
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the location of the directory that seed files can be located in.
	 *
	 * @param string $path
	 *
	 * @return Seeder
	 */
	public function setPath(string $path)
	{
		$this->seedPath = rtrim($path, '/') . '/';

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the silent treatment.
	 *
	 * @param bool $silent
	 *
	 * @return Seeder
	 */
	public function setSilent(bool $silent)
	{
		$this->silent = $silent;

		return $this;
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
