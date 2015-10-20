<?php namespace App\Config;

class ServicesConfig {

	/**
	 * -------------------------------------------------------------------
	 * SERVICES
	 * -------------------------------------------------------------------
	 * This file contains a map of name-spaced classes and their aliases.
	 * These are used by the DI class to provide instances of classes.
	 *
	 * The 'alias' (key) is how you will reference the class instance through DI.
	 * The class name (value) is the fully name-spaced class name to use.
	 * If you want to substitute a different class in place of the current one,
	 * just change the name of the class to the fully name-spaced class you
	 * want to use.
	 *
	 * Examples:
	 *      $ci = \CodeIgniter\DI::getInstance();
	 *      $bm = $ci->benchmark;
	 *      $ci->benchmark->mark('some_mark_start');
	 */
	public $services = [];

	//--------------------------------------------------------------------

	public function __construct()
	{
	    $this->services = [
		    // alias            class name
		    //--------------------------------------------------------------------

		    // The core CodeIgniter files
		    'autoloader' => '\CodeIgniter\Autoloader\Autoloader',
		    'bmtimer'    => '\CodeIgniter\Benchmark\Timer',
		    'bmiterator' => '\CodeIgniter\Benchmark\Iterator',
		    'exceptions' => '\CodeIgniter\Debug\Exceptions',
		    'logger'     => '\CodeIgniter\Log\Logger',
		    'router'     => '\CodeIgniter\Router\Router',
		    'routes'     => '\CodeIgniter\Router\RouteCollection',
		    'renderer'   => function ($di)
		    {
				return new \CodeIgniter\View\View(APPPATH.'views/');
		    },
	        'request'   => '\CodeIgniter\HTTPLite\IncomingRequest',
	        'response'  => '\CodeIgniter\HTTP\Response'

		    // Your custom files can be added here.
	    ];
	}

	//--------------------------------------------------------------------

}
