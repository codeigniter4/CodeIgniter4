<?php namespace CodeIgniter\Autoloader;

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
use Config\Autoload;

/**
 * Class Loader
 *
 * Allows loading non-class files in a namespaced manner.
 * Works with Helpers, Views, etc.
 *
  *
 * @package CodeIgniter
 */
class FileLocator
{

	/**
	 * Stores our namespaces
	 *
	 * @var array
	 */
	protected $namespaces;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param Autoload $autoload
	 */
	public function __construct(Autoload $autoload)
	{
		$this->namespaces = $autoload->psr4;

		unset($autoload);

		// Always keep the Application directory as a "package".
		array_unshift($this->namespaces, APPPATH);
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to locate a file by examining the name for a namespace
	 * and looking through the PSR-4 namespaced files that we know about.
	 *
	 * @param string $file   The namespaced file to locate
	 * @param string $folder The folder within the namespace that we should look for the file.
	 * @param string $ext    The file extension the file should have.
	 *
	 * @return string       The path to the file if found, or an empty string.
	 */
	public function locateFile(string $file, string $folder = null, string $ext = 'php'): string
	{
		// Ensure the extension is on the filename
		$file = strpos($file, '.' . $ext) !== false ? $file : $file . '.' . $ext;

		// Clean the folder name from the filename
		if ( ! empty($folder))
		{
			$file = str_replace($folder . '/', '', $file);
		}

		// No namespaceing? Try the application folder.
		if (strpos($file, '\\') === false)
		{
			return $this->legacyLocate($file, $folder);
		}

		// Standardize slashes to handle nested directories.
		$file = str_replace('/', '\\', $file);

		$segments = explode('\\', $file);

		// The first segment will be empty if a slash started the filename.
		if (empty($segments[0]))
			unset($segments[0]);

		$path = '';
		$prefix = '';
		$filename = '';

		while ( ! empty($segments))
		{
			$prefix .= empty($prefix) ? ucfirst(array_shift($segments)) : '\\' . ucfirst(array_shift($segments));

			if ( ! array_key_exists($prefix, $this->namespaces))
			{
				continue;
			}

			$path = $this->namespaces[$prefix] . '/';
			$filename = implode('/', $segments);
			break;
		}

		// IF we have a folder name, then the calling function
		// expects this file to be within that folder, like 'Views',
		// or 'libraries'.
		if ( ! empty($folder) && strpos($filename, $folder) === false)
		{
			$filename = $folder . '/' . $filename;
		}

		$path .= $filename;

		if ( ! $this->requireFile($path))
		{
			$path = '';
		}

		return $path;
	}

	//--------------------------------------------------------------------

	/**
	 * Examines a file and returns the fully qualified domain name.
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public function getClassname(string $file) : string
	{
		$php    = file_get_contents($file);
		$tokens = token_get_all($php);
		$count  = count($tokens);
		$dlm    = false;
		$namespace = '';
		$class_name = '';

		for ($i = 2; $i < $count; $i++)
		{
			if ((isset($tokens[$i-2][1]) && ($tokens[$i-2][1] == "phpnamespace" || $tokens[$i-2][1] == "namespace")) || ($dlm && $tokens[$i-1][0] == T_NS_SEPARATOR && $tokens[$i][0] == T_STRING))
			{
				if (! $dlm)
				{
					$namespace = 0;
				}
				if (isset($tokens[$i][1]))
				{
					$namespace = $namespace ? $namespace."\\".$tokens[$i][1] : $tokens[$i][1];
					$dlm       = true;
				}
			}
			elseif ($dlm && ($tokens[$i][0] != T_NS_SEPARATOR) && ($tokens[$i][0] != T_STRING))
			{
				$dlm = false;
			}
			if (($tokens[$i-2][0] == T_CLASS || (isset($tokens[$i-2][1]) && $tokens[$i-2][1] == "phpclass"))
				&& $tokens[$i-1][0] == T_WHITESPACE
				&& $tokens[$i][0] == T_STRING)
			{
				$class_name = $tokens[$i][1];
				break;
			}
		}

		if( empty( $class_name ) ) return "";

		return $namespace .'\\'. $class_name;
	}

	//--------------------------------------------------------------------

	/**
	 * Searches through all of the defined namespaces looking for a file.
	 * Returns an array of all found locations for the defined file.
	 *
	 * Example:
	 *
	 *  $locator->search('Config/Routes.php');
	 *  // Assuming PSR4 namespaces include foo and bar, might return:
	 *  [
	 *      'application/modules/foo/Config/Routes.php',
	 *      'application/modules/bar/Config/Routes.php',
	 *  ]
	 *
	 * @param string $path
	 * @param string $ext
	 *
	 * @return array
	 */
	public function search(string $path, string $ext = 'php'): array
	{
		$foundPaths = [];

		// Ensure the extension is on the filename
		$path = strpos($path, '.' . $ext) !== false ? $path : $path . '.' . $ext;

		foreach ($this->namespaces as $name => $folder)
		{
			$folder = rtrim($folder, '/') . '/';

			if (file_exists($folder . $path))
			{
				$foundPaths[] = $folder . $path;
			}
		}

		// Remove any duplicates
		$foundPaths = array_unique($foundPaths);

		return $foundPaths;
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to load a file and instantiate a new class by looking
	 * at its full path and comparing that to our existing psr4 namespaces
	 * in Autoloader config file.
	 *
	 * @param string $path
	 *
	 * @return string|void
	 */
	public function findQualifiedNameFromPath(string $path)
	{
		$path = realpath($path);

		if ( ! $path)
		{
			return;
		}

		foreach ($this->namespaces as $namespace => $nsPath)
		{
			$nsPath = realpath($nsPath);
			if (is_numeric($namespace) || empty($nsPath))
				continue;

			if (mb_strpos($path, $nsPath) === 0)
			{
				$className = '\\' . $namespace . '\\' .
						ltrim(str_replace('/', '\\', mb_substr($path, mb_strlen($nsPath))), '\\');
				// Remove the file extension (.php)
				$className = mb_substr($className, 0, -4);

				return $className;
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Scans the defined namespaces, returning a list of all files
	 * that are contained within the subpath specifed by $path.
	 *
	 * @param string $path
	 *
	 * @return array
	 */
	public function listFiles(string $path): array
	{
		if (empty($path))
			return [];

		$files = [];
		helper('filesystem');

		foreach ($this->namespaces as $namespace => $nsPath)
		{
			$fullPath = realpath(rtrim($nsPath, '/') . '/' . $path);

			if ( ! is_dir($fullPath))
				continue;

			$tempFiles = get_filenames($fullPath, true);
			//CLI::newLine($tempFiles);

			if (! empty($tempFiles))
				$files = array_merge($files, $tempFiles);
		}

		return $files;
	}

	/**
	 * Checks the application folder to see if the file can be found.
	 * Only for use with filenames that DO NOT include namespacing.
	 *
	 * @param string      $file
	 * @param string|null $folder
	 *
	 * @return string
	 * @internal param string $ext
	 *
	 */
	protected function legacyLocate(string $file, string $folder = null): string
	{
		$paths = [APPPATH, BASEPATH];

		foreach ($paths as $path)
		{
			$path .= empty($folder) ? $file : $folder . '/' . $file;

			if ($this->requireFile($path) === true)
			{
				return $path;
			}
		}

		return '';
	}

	//--------------------------------------------------------------------

	/**
	 * Checks to see if a file exists on the file system. This is split
	 * out to it's own method to make testing simpler.
	 *
	 * @codeCoverageIgnore
	 * @param string $path
	 *
	 * @return bool
	 */
	protected function requireFile(string $path): bool
	{
		return file_exists($path);
	}

	//--------------------------------------------------------------------
}
