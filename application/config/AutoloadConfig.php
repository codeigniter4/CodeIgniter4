<?php namespace App\Config;

/**
 * -------------------------------------------------------------------
 * AUTO-LOADER
 * -------------------------------------------------------------------
 * This file defines the namespaces and class maps so the Autoloader
 * can find the files as needed.
 */
class AutoloadConfig
{
	public $psr4 = [];

	public $classmap = [];

	//--------------------------------------------------------------------

	public function __construct()
	{
		/**
		 * -------------------------------------------------------------------
		 * Namespaces
		 * -------------------------------------------------------------------
		 * This maps the locations of any namespaces in your application
		 * to their location on the file system. These are used by the
		 * Autoloader to locate files the first time they have been instantiated.
		 *
		 * The '/application' and '/system' directories are already mapped for
		 * you. You may change the name of the 'App' namespace if you wish,
		 * but this should be done prior to creating any namespaced classes,
		 * else you will need to modify all of those classes for this to work.
		 *
		 * DO NOT change the name of the CodeIgniter namespace or your application
		 * WILL break. *
		 * Prototype:
		 *
		 *   $config['psr4'] = [
		 *       'CodeIgniter' => SYSPATH
		 *   `];
		 */
		$this->psr4 = [
			'App'             => realpath(APPPATH),
			'App\Config'      => APPPATH.'config',
			'App\Controllers' => APPPATH.'controllers',
			'CodeIgniter'     => realpath(BASEPATH),
		];

		/**
		 * -------------------------------------------------------------------
		 * Class Map
		 * -------------------------------------------------------------------
		 * The class map provides a map of class names and their exact
		 * location on the drive. Classes loaded in this manner will have
		 * slightly faster performance because they will not have to be
		 * searched for within one or more directories as they would if they
		 * were being autoloaded through a namespace.
		 *
		 * Prototype:
		 *
		 *   $config['classmap'] = [
		 *       'MyClass'   => '/path/to/class/file.php'
		 *   ];
		 */
		$this->classmap = [
			'CodeIgniter\Loader'                          => BASEPATH.'Loader.php',
			'CodeIgniter\Controller'                      => BASEPATH.'Controller.php',
			'CodeIgniter\Benchmark\Timer'                 => BASEPATH.'Benchmark/Timer.php',
			'CodeIgniter\Benchmark\Iterator'              => BASEPATH.'Benchmark/Iterator.php',
			'CodeIgniter\Config\BaseConfig'               => BASEPATH.'Config/BaseConfig.php',
			'CodeIgniter\HTTP\Message'                    => BASEPATH.'HTTP/Message.php',
			'CodeIgniter\HTTP\Request'                    => BASEPATH.'HTTP/Request.php',
			'CodeIgniter\HTTP\IncomingRequest'            => BASEPATH.'HTTP/IncomingRequest.php',
			'CodeIgniter\HTTP\RequestInterface'           => BASEPATH.'HTTP/RequestInterface.php',
			'CodeIgniter\HTTP\URI'                        => BASEPATH.'HTTP/URI.php',
			'CodeIgniter\HTTP\Response'                   => BASEPATH.'HTTP/Response.php',
			'CodeIgniter\HTTP\ResponseInterface'          => BASEPATH.'HTTP/ResponseInterface.php',
			'CodeIgniter\HTTP\CURLRequest'                => BASEPATH.'HTTP/CURLRequest.php',
			'CodeIgniter\Debug\Exceptions'                => BASEPATH.'Debug/Exceptions.php',
			'CodeIgniter\Router\RouteCollection'          => BASEPATH.'Router/RouteCollection.php',
			'CodeIgniter\Router\RouteCollectionInterface' => BASEPATH.'Router/RouteCollectionInterface.php',
			'CodeIgniter\Router\Router'                   => BASEPATH.'Router/Router.php',
			'CodeIgniter\Router\RouterInterface'          => BASEPATH.'Router/RouterInterface.php',
			'CodeIgniter\Log\Logger'                      => BASEPATH.'Log/Logger.php',
			'CodeIgniter\View\RenderableInterface'        => BASEPATH.'View/RenderableInterface.php',
			'CodeIgniter\View\View'                       => BASEPATH.'View/View.php',
			'Zend\Escaper\Escaper'                        => BASEPATH.'View/Escaper.php',
			'PSR\Log\LoggerInterface'                     => BASEPATH.'Log/LoggerInterface.php',
		    'PSR\Log\LogLevel'                            => BASEPATH.'Log/LogLevel.php',
		    'PSR\Log\LoggerAwareInterface'                => BASEPATH.'Log/LoggerAwareInterface.php',
		];
	}

	//--------------------------------------------------------------------

}
