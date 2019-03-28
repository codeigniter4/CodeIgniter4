<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
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
 * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 3.0.0
 * @filesource
 */

namespace CodeIgniter\Config;

/**
 * AUTO-LOADER
 *
 * This file defines the namespaces and class maps so the Autoloader
 * can find the files as needed.
 */
class AutoloadConfig
{

	/**
	 * Array of namespaces for autoloading.
	 *
	 * @var array
	 */
	public $psr4 = [];

	/**
	 * Map of class names and locations
	 *
	 * @var array
	 */
	public $classmap = [];

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 */
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
		 *   $Config['psr4'] = [
		 *       'CodeIgniter' => SYSPATH
		 *   `];
		 */
		$this->psr4 = [
			'CodeIgniter' => realpath(SYSTEMPATH),
		];

		if (isset($_SERVER['CI_ENVIRONMENT']) && $_SERVER['CI_ENVIRONMENT'] === 'testing')
		{
			$this->psr4['Tests\Support'] = SUPPORTPATH;
		}

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
		 *   $Config['classmap'] = [
		 *       'MyClass'   => '/path/to/class/file.php'
		 *   ];
		 */
		$this->classmap = [
			'CodeIgniter\CodeIgniter'                       => SYSTEMPATH . 'CodeIgniter.php',
			'CodeIgniter\CLI\CLI'                           => SYSTEMPATH . 'CLI/CLI.php',
			'CodeIgniter\Cache\CacheFactory'                => SYSTEMPATH . 'Cache/CacheFactory.php',
			'CodeIgniter\Cache\CacheInterface'              => SYSTEMPATH . 'Cache/CacheInterface.php',
			'CodeIgniter\Cache\Handlers\DummyHandler'       => SYSTEMPATH . 'Cache/Handlers/DummyHandler.php',
			'CodeIgniter\Cache\Handlers\FileHandler'        => SYSTEMPATH . 'Cache/Handlers/FileHandler.php',
			'CodeIgniter\Cache\Handlers\MemcachedHandler'   => SYSTEMPATH . 'Cache/Handlers/MemcachedHandler.php',
			'CodeIgniter\Cache\Handlers\PredisHandler'      => SYSTEMPATH . 'Cache/Handlers/PredisHandler.php',
			'CodeIgniter\Cache\Handlers\RedisHandler'       => SYSTEMPATH . 'Cache/Handlers/RedisHandler.php',
			'CodeIgniter\Cache\Handlers\WincacheHandler'    => SYSTEMPATH . 'Cache/Handlers/WincacheHandler.php',
			'CodeIgniter\Controller'                        => SYSTEMPATH . 'Controller.php',
			'CodeIgniter\Config\AutoloadConfig'             => SYSTEMPATH . 'Config/Autoload.php',
			'CodeIgniter\Config\BaseConfig'                 => SYSTEMPATH . 'Config/BaseConfig.php',
			'CodeIgniter\Config\Database'                   => SYSTEMPATH . 'Config/Database.php',
			'CodeIgniter\Config\Database\Connection'        => SYSTEMPATH . 'Config/Database/Connection.php',
			'CodeIgniter\Config\Database\Connection\MySQLi' => SYSTEMPATH . 'Config/Database/Connection/MySQLi.php',
			'CodeIgniter\Config\DotEnv'                     => SYSTEMPATH . 'Config/DotEnv.php',
			'CodeIgniter\Database\BaseBuilder'              => SYSTEMPATH . 'Database/BaseBuilder.php',
			'CodeIgniter\Database\BaseConnection'           => SYSTEMPATH . 'Database/BaseConnection.php',
			'CodeIgniter\Database\BaseResult'               => SYSTEMPATH . 'Database/BaseResult.php',
			'CodeIgniter\Database\Config'                   => SYSTEMPATH . 'Database/Config.php',
			'CodeIgniter\Database\ConnectionInterface'      => SYSTEMPATH . 'Database/ConnectionInterface.php',
			'CodeIgniter\Database\Database'                 => SYSTEMPATH . 'Database/Database.php',
			'CodeIgniter\Database\Query'                    => SYSTEMPATH . 'Database/Query.php',
			'CodeIgniter\Database\QueryInterface'           => SYSTEMPATH . 'Database/QueryInterface.php',
			'CodeIgniter\Database\ResultInterface'          => SYSTEMPATH . 'Database/ResultInterface.php',
			'CodeIgniter\Database\Migration'                => SYSTEMPATH . 'Database/Migration.php',
			'CodeIgniter\Database\MigrationRunner'          => SYSTEMPATH . 'Database/MigrationRunner.php',
			'CodeIgniter\Debug\Exceptions'                  => SYSTEMPATH . 'Debug/Exceptions.php',
			'CodeIgniter\Debug\Timer'                       => SYSTEMPATH . 'Debug/Timer.php',
			'CodeIgniter\Debug\Iterator'                    => SYSTEMPATH . 'Debug/Iterator.php',
			'CodeIgniter\Events\Events'                     => SYSTEMPATH . 'Events/Events.php',
			'CodeIgniter\HTTP\CLIRequest'                   => SYSTEMPATH . 'HTTP/CLIRequest.php',
			'CodeIgniter\HTTP\ContentSecurityPolicy'        => SYSTEMPATH . 'HTTP/ContentSecurityPolicy.php',
			'CodeIgniter\HTTP\CURLRequest'                  => SYSTEMPATH . 'HTTP/CURLRequest.php',
			'CodeIgniter\HTTP\IncomingRequest'              => SYSTEMPATH . 'HTTP/IncomingRequest.php',
			'CodeIgniter\HTTP\Message'                      => SYSTEMPATH . 'HTTP/Message.php',
			'CodeIgniter\HTTP\Negotiate'                    => SYSTEMPATH . 'HTTP/Negotiate.php',
			'CodeIgniter\HTTP\Request'                      => SYSTEMPATH . 'HTTP/Request.php',
			'CodeIgniter\HTTP\RequestInterface'             => SYSTEMPATH . 'HTTP/RequestInterface.php',
			'CodeIgniter\HTTP\Response'                     => SYSTEMPATH . 'HTTP/Response.php',
			'CodeIgniter\HTTP\ResponseInterface'            => SYSTEMPATH . 'HTTP/ResponseInterface.php',
			'CodeIgniter\HTTP\URI'                          => SYSTEMPATH . 'HTTP/URI.php',
			'CodeIgniter\Log\Logger'                        => SYSTEMPATH . 'Log/Logger.php',
			'Psr\Log\AbstractLogger'                        => SYSTEMPATH . 'ThirdParty/PSR/Log/AbstractLogger.php',
			'Psr\Log\InvalidArgumentException'              => SYSTEMPATH . 'ThirdParty/PSR/Log/InvalidArgumentException.php',
			'Psr\Log\LoggerAwareInterface'                  => SYSTEMPATH . 'ThirdParty/PSR/Log/LoggerAwareInterface.php',
			'Psr\Log\LoggerAwareTrait'                      => SYSTEMPATH . 'ThirdParty/PSR/Log/LoggerAwareTrait.php',
			'Psr\Log\LoggerInterface'                       => SYSTEMPATH . 'ThirdParty/PSR/Log/LoggerInterface.php',
			'Psr\Log\LoggerTrait'                           => SYSTEMPATH . 'ThirdParty/PSR/Log/LoggerTrait.php',
			'Psr\Log\LogLevel'                              => SYSTEMPATH . 'ThirdParty/PSR/Log/LogLevel.php',
			'Psr\Log\NullLogger'                            => SYSTEMPATH . 'ThirdParty/PSR/Log/NullLogger.php',
			'CodeIgniter\Log\Handlers\BaseHandler'          => SYSTEMPATH . 'Log/Handlers/BaseHandler.php',
			'CodeIgniter\Log\Handlers\ChromeLoggerHandler'  => SYSTEMPATH . 'Log/Handlers/ChromeLoggerHandler.php',
			'CodeIgniter\Log\Handlers\FileHandler'          => SYSTEMPATH . 'Log/Handlers/FileHandler.php',
			'CodeIgniter\Log\Handlers\HandlerInterface'     => SYSTEMPATH . 'Log/Handlers/HandlerInterface.php',
			'CodeIgniter\Router\RouteCollection'            => SYSTEMPATH . 'Router/RouteCollection.php',
			'CodeIgniter\Router\RouteCollectionInterface'   => SYSTEMPATH . 'Router/RouteCollectionInterface.php',
			'CodeIgniter\Router\Router'                     => SYSTEMPATH . 'Router/Router.php',
			'CodeIgniter\Router\RouterInterface'            => SYSTEMPATH . 'Router/RouterInterface.php',
			'CodeIgniter\Security\Security'                 => SYSTEMPATH . 'Security/Security.php',
			'CodeIgniter\Session\Session'                   => SYSTEMPATH . 'Session/Session.php',
			'CodeIgniter\Session\SessionInterface'          => SYSTEMPATH . 'Session/SessionInterface.php',
			'CodeIgniter\Session\Handlers\BaseHandler'      => SYSTEMPATH . 'Session/Handlers/BaseHandler.php',
			'CodeIgniter\Session\Handlers\FileHandler'      => SYSTEMPATH . 'Session/Handlers/FileHandler.php',
			'CodeIgniter\Session\Handlers\MemcachedHandler' => SYSTEMPATH . 'Session/Handlers/MemcachedHandler.php',
			'CodeIgniter\Session\Handlers\RedisHandler'     => SYSTEMPATH . 'Session/Handlers/RedisHandler.php',
			'CodeIgniter\View\RendererInterface'            => SYSTEMPATH . 'View/RendererInterface.php',
			'CodeIgniter\View\View'                         => SYSTEMPATH . 'View/View.php',
			'CodeIgniter\View\Parser'                       => SYSTEMPATH . 'View/Parser.php',
			'CodeIgniter\View\Cell'                         => SYSTEMPATH . 'View/Cell.php',
			'Zend\Escaper\Escaper'                          => SYSTEMPATH . 'ThirdParty/ZendEscaper/Escaper.php',
		];

		if (isset($_SERVER['CI_ENVIRONMENT']) && $_SERVER['CI_ENVIRONMENT'] === 'testing')
		{
			$this->classmap['CodeIgniter\Log\TestLogger'] = SUPPORTPATH . 'Log/TestLogger.php';
			$this->classmap['CIDatabaseTestCase']         = SUPPORTPATH . 'CIDatabaseTestCase.php';
		}
	}

	//--------------------------------------------------------------------
}
