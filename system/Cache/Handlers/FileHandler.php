<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Cache\Handlers;

use CodeIgniter\Cache\Exceptions\CacheException;
use Config\Cache;
use Throwable;

/**
 * File system cache handler
 */
class FileHandler extends BaseHandler
{
	/**
	 * Maximum key length.
	 */
	public const MAX_KEY_LENGTH = 255;

	/**
	 * Where to store cached files on the disk.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Mode for the stored files.
	 * Must be chmod-safe (octal).
	 *
	 * @var integer
	 *
	 * @see https://www.php.net/manual/en/function.chmod.php
	 */
	protected $mode;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param  Cache $config
	 * @throws CacheException
	 */
	public function __construct(Cache $config)
	{
		if (! property_exists($config, 'file'))
		{
			$config->file = [
				'storePath' => $config->storePath ?? WRITEPATH . 'cache',
				'mode'      => 0640,
			];
		}

		$this->path = ! empty($config->file['storePath']) ? $config->file['storePath'] : WRITEPATH . 'cache';
		$this->path = rtrim($this->path, '/') . '/';

		if (! is_really_writable($this->path))
		{
			throw CacheException::forUnableToWrite($this->path);
		}

		$this->mode   = $config->file['mode'] ?? 0640;
		$this->prefix = $config->prefix;
	}

	//--------------------------------------------------------------------

	/**
	 * Takes care of any handler-specific setup that must be done.
	 */
	public function initialize()
	{
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
		$key  = static::validateKey($key, $this->prefix);
		$data = $this->getItem($key);

		return is_array($data) ? $data['data'] : null;
	}

	//--------------------------------------------------------------------

	/**
	 * Saves an item to the cache store.
	 *
	 * @param string  $key   Cache item name
	 * @param mixed   $value The data to save
	 * @param integer $ttl   Time To Live, in seconds (default 60)
	 *
	 * @return boolean Success or failure
	 */
	public function save(string $key, $value, int $ttl = 60)
	{
		$key = static::validateKey($key, $this->prefix);

		$contents = [
			'time' => time(),
			'ttl'  => $ttl,
			'data' => $value,
		];

		if ($this->writeFile($this->path . $key, serialize($contents)))
		{
			try
			{
				chmod($this->path . $key, $this->mode);
			}
			// @codeCoverageIgnoreStart
			catch (Throwable $e)
			{
				log_message('debug', 'Failed to set mode on cache file: ' . $e->getMessage());
			}
			// @codeCoverageIgnoreEnd

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
	 * @return boolean Success or failure
	 */
	public function delete(string $key)
	{
		$key = static::validateKey($key, $this->prefix);

		return is_file($this->path . $key) && unlink($this->path . $key);
	}

	//--------------------------------------------------------------------

	/**
	 * Deletes items from the cache store matching a given pattern.
	 *
	 * @param string $pattern Cache items glob-style pattern
	 *
	 * @return integer The number of deleted items
	 */
	public function deleteMatching(string $pattern)
	{
		$deleted = 0;

		foreach (glob($this->path . $pattern, GLOB_NOSORT) as $filename)
		{
			if (is_file($filename) && @unlink($filename))
			{
				$deleted++;
			}
		}

		return $deleted;
	}

	//--------------------------------------------------------------------

	/**
	 * Performs atomic incrementation of a raw stored value.
	 *
	 * @param string  $key    Cache ID
	 * @param integer $offset Step/value to increase by
	 *
	 * @return boolean
	 */
	public function increment(string $key, int $offset = 1)
	{
		$key  = static::validateKey($key, $this->prefix);
		$data = $this->getItem($key);

		if ($data === false)
		{
			$data = [
				'data' => 0,
				'ttl'  => 60,
			];
		}
		elseif (! is_int($data['data']))
		{
			return false;
		}

		$newValue = $data['data'] + $offset;

		return $this->save($key, $newValue, $data['ttl']) ? $newValue : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Performs atomic decrementation of a raw stored value.
	 *
	 * @param string  $key    Cache ID
	 * @param integer $offset Step/value to increase by
	 *
	 * @return boolean
	 */
	public function decrement(string $key, int $offset = 1)
	{
		$key  = static::validateKey($key, $this->prefix);
		$data = $this->getItem($key);

		if ($data === false)
		{
			$data = [
				'data' => 0,
				'ttl'  => 60,
			];
		}
		elseif (! is_int($data['data']))
		{
			return false;
		}

		$newValue = $data['data'] - $offset;

		return $this->save($key, $newValue, $data['ttl']) ? $newValue : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Will delete all items in the entire cache.
	 *
	 * @return boolean Success or failure
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
	 * @return array|false
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
	 * @return array|false|null
	 *   Returns null if the item does not exist, otherwise array<string, mixed>
	 *   with at least the 'expire' key for absolute epoch expiry (or null).
	 *   Some handlers may return false when an item does not exist, which is deprecated.
	 */
	public function getMetaData(string $key)
	{
		$key = static::validateKey($key, $this->prefix);

		if (! is_file($this->path . $key))
		{
			return false; // This will return null in a future release
		}

		$data = @unserialize(file_get_contents($this->path . $key));

		if (! is_array($data) || ! isset($data['ttl']))
		{
			return false; // This will return null in a future release
		}

		// Consider expired items as missing
		$expire = $data['time'] + $data['ttl'];

		// @phpstan-ignore-next-line
		if ($data['ttl'] > 0 && time() > $expire)
		{
			// If the file is still there then remove it
			if (is_file($this->path . $key))
			{
				unlink($this->path . $key);
			}

			return false; // This will return null in a future release
		}

		return [
			'expire' => $expire,
			'mtime'  => filemtime($this->path . $key),
			'data'   => $data['data'],
		];
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
	 * @param string $filename
	 *
	 * @return boolean|mixed
	 */
	protected function getItem(string $filename)
	{
		if (! is_file($this->path . $filename))
		{
			return false;
		}

		$data = unserialize(file_get_contents($this->path . $filename));

		// @phpstan-ignore-next-line
		if ($data['ttl'] > 0 && time() > $data['time'] + $data['ttl'])
		{
			// If the file is still there then remove it
			if (is_file($this->path . $filename))
			{
				unlink($this->path . $filename);
			}

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
	 * @param string $path
	 * @param string $data
	 * @param string $mode
	 *
	 * @return boolean
	 */
	protected function writeFile($path, $data, $mode = 'wb')
	{
		if (($fp = @fopen($path, $mode)) === false)
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
	 * @param string  $path   File path
	 * @param boolean $delDir Whether to delete any directories found in the path
	 * @param boolean $htdocs Whether to skip deleting .htaccess and index page files
	 * @param integer $_level Current directory depth level (default: 0; internal use only)
	 *
	 * @return boolean
	 */
	protected function deleteFiles(string $path, bool $delDir = false, bool $htdocs = false, int $_level = 0): bool
	{
		// Trim the trailing slash
		$path = rtrim($path, '/\\');

		if (! $currentDir = @opendir($path))
		{
			return false;
		}

		while (false !== ($filename = @readdir($currentDir)))
		{
			if ($filename !== '.' && $filename !== '..')
			{
				if (is_dir($path . DIRECTORY_SEPARATOR . $filename) && $filename[0] !== '.')
				{
					$this->deleteFiles($path . DIRECTORY_SEPARATOR . $filename, $delDir, $htdocs, $_level + 1);
				}
				elseif ($htdocs !== true || ! preg_match('/^(\.htaccess|index\.(html|htm|php)|web\.config)$/i', $filename))
				{
					@unlink($path . DIRECTORY_SEPARATOR . $filename);
				}
			}
		}

		closedir($currentDir);

		return ($delDir === true && $_level > 0) ? @rmdir($path) : true;
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
	 * @param string  $sourceDir    Path to source
	 * @param boolean $topLevelOnly Look only at the top level directory specified?
	 * @param boolean $_recursion   Internal variable to determine recursion status - do not use in calls
	 *
	 * @return array|false
	 */
	protected function getDirFileInfo(string $sourceDir, bool $topLevelOnly = true, bool $_recursion = false)
	{
		static $_filedata = [];
		$relativePath     = $sourceDir;

		if ($fp = @opendir($sourceDir))
		{
			// reset the array and make sure $source_dir has a trailing slash on the initial call
			if ($_recursion === false)
			{
				$_filedata = [];
				$sourceDir = rtrim(realpath($sourceDir) ?: $sourceDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			}

			// Used to be foreach (scandir($source_dir, 1) as $file), but scandir() is simply not as fast
			while (false !== ($file = readdir($fp)))
			{
				if (is_dir($sourceDir . $file) && $file[0] !== '.' && $topLevelOnly === false)
				{
					$this->getDirFileInfo($sourceDir . $file . DIRECTORY_SEPARATOR, $topLevelOnly, true);
				}
				elseif ($file[0] !== '.')
				{
					$_filedata[$file]                  = $this->getFileInfo($sourceDir . $file);
					$_filedata[$file]['relative_path'] = $relativePath;
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
	 * @param string $file           Path to file
	 * @param mixed  $returnedValues Array or comma separated string of information returned
	 *
	 * @return array|false
	 */
	protected function getFileInfo(string $file, $returnedValues = ['name', 'server_path', 'size', 'date'])
	{
		if (! is_file($file))
		{
			return false;
		}

		if (is_string($returnedValues))
		{
			$returnedValues = explode(',', $returnedValues);
		}

		$fileInfo = [];

		foreach ($returnedValues as $key)
		{
			switch ($key)
			{
				case 'name':
					$fileInfo['name'] = basename($file);
					break;
				case 'server_path':
					$fileInfo['server_path'] = $file;
					break;
				case 'size':
					$fileInfo['size'] = filesize($file);
					break;
				case 'date':
					$fileInfo['date'] = filemtime($file);
					break;
				case 'readable':
					$fileInfo['readable'] = is_readable($file);
					break;
				case 'writable':
					$fileInfo['writable'] = is_writable($file);
					break;
				case 'executable':
					$fileInfo['executable'] = is_executable($file);
					break;
				case 'fileperms':
					$fileInfo['fileperms'] = fileperms($file);
					break;
			}
		}

		return $fileInfo;
	}
}
