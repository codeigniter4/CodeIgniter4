<?php namespace CodeIgniter\Cache\Handlers;

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
use CodeIgniter\Cache\CacheInterface;

class FileHandler implements CacheInterface
{

	/**
	 * Prefixed to all cache names.
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Where to store cached files on the disk.
	 *
	 * @var string
	 */
	protected $path;

	//--------------------------------------------------------------------

	public function __construct($config)
	{
		$this->prefix = $config->prefix ?: '';
		$this->path = ! empty($config->storePath) ? $config->storePath : WRITEPATH . 'cache';

		$this->path = rtrim($this->path, '/') . '/';
	}

	//--------------------------------------------------------------------

	/**
	 * Takes care of any handler-specific setup that must be done.
	 */
	public function initialize()
	{
		// Not to see here...
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to fetch an item from the cache store.
	 *
	 * @param string $key Cache item name
	 *
	 * @return mixed
	 */
	public function get(string $key)
	{
		$key = $this->prefix . $key;

		$data = $this->getItem($key);

		return is_array($data) ? $data['data'] : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Saves an item to the cache store.
	 *
	 * @param string $key   Cache item name
	 * @param mixed  $value The data to save
	 * @param int    $ttl   Time To Live, in seconds (default 60)
	 *
	 * @return mixed
	 */
	public function save(string $key, $value, int $ttl = 60)
	{
		$key = $this->prefix . $key;

		$contents = [
			'time'	 => time(),
			'ttl'	 => $ttl,
			'data'	 => $value,
		];

		if ($this->writeFile($this->path . $key, serialize($contents)))
		{
			chmod($this->path . $key, 0640);

			return true;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Deletes a specific item from the cache store.
	 *
	 * @param string $key Cache item name
	 *
	 * @return mixed
	 */
	public function delete(string $key)
	{
		$key = $this->prefix . $key;

		return file_exists($this->path . $key) ? unlink($this->path . $key) : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Performs atomic incrementation of a raw stored value.
	 *
	 * @param string $key    Cache ID
	 * @param int    $offset Step/value to increase by
	 *
	 * @return mixed
	 */
	public function increment(string $key, int $offset = 1)
	{
		$key = $this->prefix . $key;

		$data = $this->getItem($key);

		if ($data === false)
		{
			$data = ['data' => 0, 'ttl' => 60];
		}
		elseif ( ! is_int($data['data']))
		{
			return false;
		}

		$new_value = $data['data'] + $offset;

		return $this->save($key, $new_value, $data['ttl']) ? $new_value : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Performs atomic decrementation of a raw stored value.
	 *
	 * @param string $key    Cache ID
	 * @param int    $offset Step/value to increase by
	 *
	 * @return mixed
	 */
	public function decrement(string $key, int $offset = 1)
	{
		$key = $this->prefix . $key;

		$data = $this->getItem($key);

		if ($data === false)
		{
			$data = ['data' => 0, 'ttl' => 60];
		}
		elseif ( ! is_int($data['data']))
		{
			return false;
		}

		$new_value = $data['data'] - $offset;

		return $this->save($key, $new_value, $data['ttl']) ? $new_value : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Will delete all items in the entire cache.
	 *
	 * @return mixed
	 */
	public function clean()
	{
		return $this->deleteFiles($this->path, false, true);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns information on the entire cache.
	 *
	 * The information returned and the structure of the data
	 * varies depending on the handler.
	 *
	 * @return mixed
	 */
	public function getCacheInfo()
	{
		return $this->getDirFileInfo($this->path);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns detailed information about the specific item in the cache.
	 *
	 * @param string $key Cache item name.
	 *
	 * @return mixed
	 */
	public function getMetaData(string $key)
	{
		$key = $this->prefix . $key;

		if ( ! file_exists($this->path . $key))
		{
			return FALSE;
		}

		$data = @unserialize(file_get_contents($this->path . $key));

		if (is_array($data))
		{
			$mtime = filemtime($this->path . $key);

			if ( ! isset($data['ttl']))
			{
				return FALSE;
			}

			return [
				'expire' => $mtime + $data['ttl'],
				'mtime'	 => $mtime,
				'data'	 => $data['data'],
			];
		}

		return FALSE;
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if the driver is supported on this system.
	 *
	 * @return boolean
	 */
	public function isSupported(): bool
	{
		return is_writable($this->path);
	}

	//--------------------------------------------------------------------

	/**
	 * Does the heavy lifting of actually retrieving the file and
	 * verifying it's age.
	 *
	 * @param string $key
	 *
	 * @return bool|mixed
	 */
	protected function getItem(string $key)
	{
		if ( ! is_file($this->path . $key))
		{
			return false;
		}

		$data = unserialize(file_get_contents($this->path . $key));

		if ($data['ttl'] > 0 && time() > $data['time'] + $data['ttl'])
		{
			unlink($this->path . $key);

			return false;
		}

		return $data;
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// SUPPORT METHODS FOR FILES
	//--------------------------------------------------------------------

	/**
	 * Writes a file to disk, or returns false if not successful.
	 *
	 * @param        $path
	 * @param        $data
	 * @param string $mode
	 *
	 * @return bool
	 */
	protected function writeFile($path, $data, $mode = 'wb')
	{
		try
		{
			if (($fp = @fopen($path, $mode)) === false)
			{
				return false;
			}
		}
		catch (\ErrorException $e)
		{
			return false;
		}

		flock($fp, LOCK_EX);

		for ($result = $written = 0, $length = strlen($data); $written < $length; $written += $result)
		{
			if (($result = fwrite($fp, substr($data, $written))) === false)
			{
				break;
			}
		}

		flock($fp, LOCK_UN);
		fclose($fp);

		return is_int($result);
	}

	//--------------------------------------------------------------------

	/**
	 * Delete Files
	 *
	 * Deletes all files contained in the supplied directory path.
	 * Files must be writable or owned by the system in order to be deleted.
	 * If the second parameter is set to TRUE, any directories contained
	 * within the supplied base directory will be nuked as well.
	 *
	 * @param    string $path    File path
	 * @param    bool   $del_dir Whether to delete any directories found in the path
	 * @param    bool   $htdocs  Whether to skip deleting .htaccess and index page files
	 * @param    int    $_level  Current directory depth level (default: 0; internal use only)
	 *
	 * @return    bool
	 */
	protected function deleteFiles($path, $del_dir = false, $htdocs = false, $_level = 0)
	{
		// Trim the trailing slash
		$path = rtrim($path, '/\\');

		if ( ! $current_dir = @opendir($path))
		{
			return false;
		}

		while (false !== ($filename = @readdir($current_dir)))
		{
			if ($filename !== '.' && $filename !== '..')
			{
				if (is_dir($path . DIRECTORY_SEPARATOR . $filename) && $filename[0] !== '.')
				{
					$this->deleteFiles($path . DIRECTORY_SEPARATOR . $filename, $del_dir, $htdocs, $_level + 1);
				}
				elseif ($htdocs !== true || ! preg_match('/^(\.htaccess|index\.(html|htm|php)|web\.config)$/i', $filename))
				{
					@unlink($path . DIRECTORY_SEPARATOR . $filename);
				}
			}
		}

		closedir($current_dir);

		return ($del_dir === true && $_level > 0) ? @rmdir($path) : true;
	}

	//--------------------------------------------------------------------

	/**
	 * Get Directory File Information
	 *
	 * Reads the specified directory and builds an array containing the filenames,
	 * filesize, dates, and permissions
	 *
	 * Any sub-folders contained within the specified path are read as well.
	 *
	 * @param    string $source_dir     Path to source
	 * @param    bool   $top_level_only Look only at the top level directory specified?
	 * @param    bool   $_recursion     Internal variable to determine recursion status - do not use in calls
	 *
	 * @return    array|false
	 */
	protected function getDirFileInfo($source_dir, $top_level_only = true, $_recursion = false)
	{
		static $_filedata = [];
		$relative_path = $source_dir;

		if ($fp = @opendir($source_dir))
		{
			// reset the array and make sure $source_dir has a trailing slash on the initial call
			if ($_recursion === false)
			{
				$_filedata = [];
				$source_dir = rtrim(realpath($source_dir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			}

			// Used to be foreach (scandir($source_dir, 1) as $file), but scandir() is simply not as fast
			while (false !== ($file = readdir($fp)))
			{
				if (is_dir($source_dir . $file) && $file[0] !== '.' && $top_level_only === false)
				{
					$this->getDirFileInfo($source_dir . $file . DIRECTORY_SEPARATOR, $top_level_only, true);
				}
				elseif ($file[0] !== '.')
				{
					$_filedata[$file] = $this->getFileInfo($source_dir . $file);
					$_filedata[$file]['relative_path'] = $relative_path;
				}
			}

			closedir($fp);

			return $_filedata;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Get File Info
	 *
	 * Given a file and path, returns the name, path, size, date modified
	 * Second parameter allows you to explicitly declare what information you want returned
	 * Options are: name, server_path, size, date, readable, writable, executable, fileperms
	 * Returns FALSE if the file cannot be found.
	 *
	 * @param    string $file            Path to file
	 * @param    mixed  $returned_values Array or comma separated string of information returned
	 *
	 * @return    array|false
	 */
	protected function getFileInfo(string $file, array $returned_values = ['name', 'server_path', 'size', 'date'])
	{
		if ( ! file_exists($file))
		{
			return false;
		}

		foreach ($returned_values as $key)
		{
			switch ($key)
			{
				case 'name':
					$fileinfo['name'] = basename($file);
					break;
				case 'server_path':
					$fileinfo['server_path'] = $file;
					break;
				case 'size':
					$fileinfo['size'] = filesize($file);
					break;
				case 'date':
					$fileinfo['date'] = filemtime($file);
					break;
				case 'readable':
					$fileinfo['readable'] = is_readable($file);
					break;
				case 'writable':
					$fileinfo['writable'] = is_writable($file);
					break;
				case 'executable':
					$fileinfo['executable'] = is_executable($file);
					break;
				case 'fileperms':
					$fileinfo['fileperms'] = fileperms($file);
					break;
			}
		}

		return $fileinfo;
	}

	//--------------------------------------------------------------------
}
