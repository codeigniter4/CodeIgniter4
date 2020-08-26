<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
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
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Autoloader;

/**
 * Class FileLocator
 *
 * Allows loading non-class files in a namespaced manner.
 * Works with Helpers, Views, etc.
 *
 * @package CodeIgniter
 */
class FileLocator
{
	/**
	 * The Autoloader to use.
	 *
	 * @var \CodeIgniter\Autoloader\Autoloader
	 */
	protected $autoloader;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param Autoloader $autoloader
	 */
	public function __construct(Autoloader $autoloader)
	{
		$this->autoloader = $autoloader;
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
	 * @return string|false The path to the file, or false if not found.
	 */
	public function locateFile(string $file, string $folder = null, string $ext = 'php')
	{
		$file = $this->ensureExt($file, $ext);

		// Clears the folder name if it is at the beginning of the filename
		if (! empty($folder) && ($pos = strpos($file, $folder)) === 0)
		{
			$file = substr($file, strlen($folder . '/'));
		}

		// Is not namespaced? Try the application folder.
		if (strpos($file, '\\') === false)
		{
			return $this->legacyLocate($file, $folder);
		}

		// Standardize slashes to handle nested directories.
		$file = strtr($file, '/', '\\');

		$segments = explode('\\', $file);

		// The first segment will be empty if a slash started the filename.
		if (empty($segments[0]))
		{
			unset($segments[0]);
		}

		$paths    = [];
		$prefix   = '';
		$filename = '';

		// Namespaces always comes with arrays of paths
		$namespaces = $this->autoloader->getNamespace();

		while (! empty($segments))
		{
			$prefix .= empty($prefix) ? array_shift($segments) : '\\' . array_shift($segments);

			if (empty($namespaces[$prefix]))
			{
				continue;
			}
			$paths = $namespaces[$prefix];

			$filename = implode('/', $segments);
			break;
		}

		// if no namespaces matched then quit
		if (empty($paths))
		{
			return false;
		}

		// Check each path in the namespace
		foreach ($paths as $path)
		{
			// Ensure trailing slash
			$path = rtrim($path, '/') . '/';

			// If we have a folder name, then the calling function
			// expects this file to be within that folder, like 'Views',
			// or 'libraries'.
			if (! empty($folder) && strpos($path . $filename, '/' . $folder . '/') === false)
			{
				$path .= trim($folder, '/') . '/';
			}

			$path .= $filename;
			if (is_file($path))
			{
				return $path;
			}
		}

		return false;
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
		$php        = file_get_contents($file);
		$tokens     = token_get_all($php);
		$dlm        = false;
		$namespace  = '';
		$class_name = '';

		foreach ($tokens as $i => $token)
		{
			if ($i < 2)
			{
				continue;
			}

			if ((isset($tokens[$i - 2][1]) && ($tokens[$i - 2][1] === 'phpnamespace' || $tokens[$i - 2][1] === 'namespace')) || ($dlm && $tokens[$i - 1][0] === T_NS_SEPARATOR && $token[0] === T_STRING))
			{
				if (! $dlm)
				{
					$namespace = 0;
				}
				if (isset($token[1]))
				{
					$namespace = $namespace ? $namespace . '\\' . $token[1] : $token[1];
					$dlm       = true;
				}
			}
			elseif ($dlm && ($token[0] !== T_NS_SEPARATOR) && ($token[0] !== T_STRING))
			{
				$dlm = false;
			}
			if (($tokens[$i - 2][0] === T_CLASS || (isset($tokens[$i - 2][1]) && $tokens[$i - 2][1] === 'phpclass'))
				&& $tokens[$i - 1][0] === T_WHITESPACE
				&& $token[0] === T_STRING)
			{
				$class_name = $token[1];
				break;
			}
		}

		if (empty( $class_name ))
		{
			return '';
		}

		return $namespace . '\\' . $class_name;
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
	 *      'app/Modules/foo/Config/Routes.php',
	 *      'app/Modules/bar/Config/Routes.php',
	 *  ]
	 *
	 * @param string  $path
	 * @param string  $ext
	 * @param boolean $prioritizeApp
	 *
	 * @return array
	 */
	public function search(string $path, string $ext = 'php', bool $prioritizeApp = true): array
	{
		$path = $this->ensureExt($path, $ext);

		$foundPaths = [];
		$appPaths   = [];

		foreach ($this->getNamespaces() as $namespace)
		{
			if (isset($namespace['path']) && is_file($namespace['path'] . $path))
			{
				$fullPath = $namespace['path'] . $path;
				if ($prioritizeApp)
				{
					$foundPaths[] = $fullPath;
				}
				else
				{
					if (strpos($fullPath, APPPATH) === 0)
					{
						$appPaths[] = $fullPath;
					}
					else
					{
						$foundPaths[] = $fullPath;
					}
				}
			}
		}

		if (! $prioritizeApp && ! empty($appPaths))
		{
			$foundPaths = array_merge($foundPaths, $appPaths);
		}

		// Remove any duplicates
		$foundPaths = array_unique($foundPaths);

		return $foundPaths;
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures a extension is at the end of a filename
	 *
	 * @param string $path
	 * @param string $ext
	 *
	 * @return string
	 */
	protected function ensureExt(string $path, string $ext): string
	{
		if ($ext)
		{
			$ext = '.' . $ext;

			if (substr($path, -strlen($ext)) !== $ext)
			{
				$path .= $ext;
			}
		}

		return $path;
	}

	//--------------------------------------------------------------------

	/**
	 * Return the namespace mappings we know about.
	 *
	 * @return array|string
	 */
	protected function getNamespaces()
	{
		$namespaces = [];

		// Save system for last
		$system = [];

		foreach ($this->autoloader->getNamespace() as $prefix => $paths)
		{
			foreach ($paths as $path)
			{
				if ($prefix === 'CodeIgniter')
				{
					$system = [
						'prefix' => $prefix,
						'path'   => rtrim($path, '\\/') . DIRECTORY_SEPARATOR,
					];

					continue;
				}

				$namespaces[] = [
					'prefix' => $prefix,
					'path'   => rtrim($path, '\\/') . DIRECTORY_SEPARATOR,
				];
			}
		}

		$namespaces[] = $system;

		return $namespaces;
	}

	//--------------------------------------------------------------------

	/**
	 * Find the qualified name of a file according to
	 * the namespace of the first matched namespace path.
	 *
	 * @param string $path
	 *
	 * @return string|false The qualified name or false if the path is not found
	 */
	public function findQualifiedNameFromPath(string $path)
	{
		$path = realpath($path);

		if (! $path)
		{
			return false;
		}

		foreach ($this->getNamespaces() as $namespace)
		{
			$namespace['path'] = realpath($namespace['path']);

			if (empty($namespace['path']))
			{
				continue;
			}

			if (mb_strpos($path, $namespace['path']) === 0)
			{
				$className = '\\' . $namespace['prefix'] . '\\' .
						ltrim(str_replace('/', '\\', mb_substr(
							$path, mb_strlen($namespace['path']))
						), '\\');
				// Remove the file extension (.php)
				$className = mb_substr($className, 0, -4);

				// Check if this exists
				if (class_exists($className))
				{
					return $className;
				}
			}
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Scans the defined namespaces, returning a list of all files
	 * that are contained within the subpath specified by $path.
	 *
	 * @param string $path
	 *
	 * @return array
	 */
	public function listFiles(string $path): array
	{
		if (empty($path))
		{
			return [];
		}

		$files = [];
		helper('filesystem');

		foreach ($this->getNamespaces() as $namespace)
		{
			$fullPath = realpath($namespace['path'] . $path);

			if (! is_dir($fullPath))
			{
				continue;
			}

			$tempFiles = get_filenames($fullPath, true);

			if (! empty($tempFiles))
			{
				$files = array_merge($files, $tempFiles);
			}
		}

		return $files;
	}

	//--------------------------------------------------------------------

	/**
	 * Scans the provided namespace, returning a list of all files
	 * that are contained within the subpath specified by $path.
	 *
	 * @param string $prefix
	 * @param string $path
	 *
	 * @return array
	 */
	public function listNamespaceFiles(string $prefix, string $path): array
	{
		if (empty($path) || empty($prefix))
		{
			return [];
		}

		$files = [];
		helper('filesystem');

		// autoloader->getNamespace($prefix) returns an array of paths for that namespace
		foreach ($this->autoloader->getNamespace($prefix) as $namespacePath)
		{
			$fullPath = realpath(rtrim($namespacePath, '/') . '/' . $path);

			if (! is_dir($fullPath))
			{
				continue;
			}

			$tempFiles = get_filenames($fullPath, true);

			if (! empty($tempFiles))
			{
				$files = array_merge($files, $tempFiles);
			}
		}

		return $files;
	}

	//--------------------------------------------------------------------

	/**
	 * Checks the app folder to see if the file can be found.
	 * Only for use with filenames that DO NOT include namespacing.
	 *
	 * @param string      $file
	 * @param string|null $folder
	 *
	 * @return string|false The path to the file, or false if not found.
	 */
	protected function legacyLocate(string $file, string $folder = null)
	{
		$path = realpath(APPPATH . (empty($folder) ? $file : $folder . '/' . $file));

		if (is_file($path))
		{
			return $path;
		}

		return false;
	}
}
