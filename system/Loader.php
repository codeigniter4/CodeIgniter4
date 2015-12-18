<?php namespace CodeIgniter;

use App\Config\AutoloadConfig;

/**
 * Class Loader
 *
 * Allows loading non-class files in a namespaced manner.
 * Works with Helpers, Views, etc.
 *
 * @package CodeIgniter
 */
class Loader {

	/**
	 * Stores our namespaces
	 *
	 * @var array
	 */
	protected $namespaces;

	//--------------------------------------------------------------------

	public function __construct(AutoloadConfig $autoload)
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
	 *
	 * @return string       The path to the file if found, or an empty string.
	 */
	public function locateFile(string $file, string $folder=null): string
	{
		// No namespaceing? Get out.
		if (strpos($file, '\\') === false) return '';

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

			$path = realpath($this->namespaces[$prefix]).'/';
			$filename = implode('/', $segments);
			break;
		}

		// IF we have a folder name, then the calling function
		// expects this file to be within that folder, like 'views',
		// or 'libraries'.
		if (! empty($folder))
		{
			// Strip the folder from the filename if it's there.
			if (strpos($filename, $folder) === 0)
			{
				$filename = substr($filename, strlen($folder) +1);
			}

			$filename = $folder.'/'.$filename;
		}

		$path .= $filename;

		if (! file_exists($path))
		{
			$path = '';
		}

		return $path;
	}

	//--------------------------------------------------------------------


	//--------------------------------------------------------------------
	// Helpers
	//--------------------------------------------------------------------


	//--------------------------------------------------------------------
	// Views
	//--------------------------------------------------------------------


}