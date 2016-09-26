<?php namespace CodeIgniter\Autoloader;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
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
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
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
 * @todo sanitize filenames prior to checking them...
 *
 * @package CodeIgniter
 */
class FileLocator {

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
	public function locateFile(string $file, string $folder=null, string $ext = 'php'): string
	{
		// Ensure the extension is on the filename
		$file = strpos($file, '.'.$ext) !== false
				? $file
				: $file.'.'.$ext;

		// Clean the folder name from the filename
		if (! empty($folder))
		{
			$file = str_replace($folder.'/', '', $file);
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
		if (empty($segments[0])) unset($segments[0]);

		$path     = '';
		$prefix   = '';
		$filename = '';

		while (! empty($segments))
		{
			$prefix .= empty($prefix)
					? ucfirst(array_shift($segments))
					: '\\'. ucfirst(array_shift($segments));

			if (! array_key_exists($prefix, $this->namespaces))
			{
				continue;
			}

			$path = $this->namespaces[$prefix].'/';
			$filename = implode('/', $segments);
			break;
		}

		// IF we have a folder name, then the calling function
		// expects this file to be within that folder, like 'Views',
		// or 'libraries'.
		// @todo Allow it to check with and without the nested folder.
		if (! empty($folder) && strpos($filename, $folder) === false)
		{
			$filename = $folder.'/'.$filename;
		}

		$path .= $filename;

		if (! $this->requireFile($path))
		{
			$path = '';
		}

		return $path;
	}

	//--------------------------------------------------------------------

	/**
	 * Checks the application folder to see if the file can be found.
	 * Only for use with filenames that DO NOT include namespacing.
	 *
	 * @param string      $file
	 * @param string|null $folder
	 * @param string      $ext
	 *
	 * @return string
	 */
	protected function legacyLocate(string $file, string $folder=null): string
	{
		$paths = [APPPATH, BASEPATH];

		foreach ($paths as $path)
		{
			$path .= empty($folder)
				? $file
				: $folder.'/'.$file;

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
