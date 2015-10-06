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

	protected $namespaces;

	//--------------------------------------------------------------------

	public function __construct(AutoloadConfig $autoload)
	{
	    $this->namespaces = $autoload->psr4;

		unset($autoload);

		// Always keep the Application directory as a "package".
		array_unshift($this->packagePaths, APPPATH);
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Helpers
	//--------------------------------------------------------------------


	//--------------------------------------------------------------------
	// Views
	//--------------------------------------------------------------------


}