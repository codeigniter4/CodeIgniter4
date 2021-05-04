<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Config;

/**
 * AUTOLOADER CONFIGURATION
 *
 * This file defines the namespaces and class maps so the Autoloader
 * can find the files as needed.
 */
class AutoloadConfig
{
	/**
	 * -------------------------------------------------------------------
	 * Namespaces
	 * -------------------------------------------------------------------
	 * This maps the locations of any namespaces in your application to
	 * their location on the file system. These are used by the autoloader
	 * to locate files the first time they have been instantiated.
	 *
	 * The '/app' and '/system' directories are already mapped for you.
	 * you may change the name of the 'App' namespace if you wish,
	 * but this should be done prior to creating any namespaced classes,
	 * else you will need to modify all of those classes for this to work.
	 *
	 * @var array<string, string>
	 */
	public $psr4 = [];

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
	 * @var array<string, string>
	 */
	public $classmap = [];

	/**
	 * -------------------------------------------------------------------
	 * Files
	 * -------------------------------------------------------------------
	 * The files array provides a list of paths to __non-class__ files
	 * that will be autoloaded. This can be useful for bootstrap operations
	 * or for loading functions.
	 *
	 * @var array<int, string>
	 */
	public $files = [];

	/**
	 * -------------------------------------------------------------------
	 * Namespaces
	 * -------------------------------------------------------------------
	 * This maps the locations of any namespaces in your application to
	 * their location on the file system. These are used by the autoloader
	 * to locate files the first time they have been instantiated.
	 *
	 * Do not change the name of the CodeIgniter namespace or your application
	 * will break.
	 *
	 * @var array<string, string>
	 */
	protected $corePsr4 = [
		'CodeIgniter' => SYSTEMPATH,
		'App'         => APPPATH, // To ensure filters, etc still found,
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
	 * @var array<string, string>
	 */
	protected $coreClassmap = [
		'Psr\Log\AbstractLogger'           => SYSTEMPATH . 'ThirdParty/PSR/Log/AbstractLogger.php',
		'Psr\Log\InvalidArgumentException' => SYSTEMPATH . 'ThirdParty/PSR/Log/InvalidArgumentException.php',
		'Psr\Log\LoggerAwareInterface'     => SYSTEMPATH . 'ThirdParty/PSR/Log/LoggerAwareInterface.php',
		'Psr\Log\LoggerAwareTrait'         => SYSTEMPATH . 'ThirdParty/PSR/Log/LoggerAwareTrait.php',
		'Psr\Log\LoggerInterface'          => SYSTEMPATH . 'ThirdParty/PSR/Log/LoggerInterface.php',
		'Psr\Log\LoggerTrait'              => SYSTEMPATH . 'ThirdParty/PSR/Log/LoggerTrait.php',
		'Psr\Log\LogLevel'                 => SYSTEMPATH . 'ThirdParty/PSR/Log/LogLevel.php',
		'Psr\Log\NullLogger'               => SYSTEMPATH . 'ThirdParty/PSR/Log/NullLogger.php',
		'Laminas\Escaper\Escaper'          => SYSTEMPATH . 'ThirdParty/Escaper/Escaper.php',
	];

	/**
	 * -------------------------------------------------------------------
	 * Core Files
	 * -------------------------------------------------------------------
	 * List of files from the framework to be autoloaded early.
	 *
	 * @var array<int, string>
	 */
	protected $coreFiles = [];

	/**
	 * Constructor.
	 *
	 * Merge the built-in and developer-configured psr4 and classmap,
	 * with preference to the developer ones.
	 */
	public function __construct()
	{
		if (isset($_SERVER['CI_ENVIRONMENT']) && $_SERVER['CI_ENVIRONMENT'] === 'testing')
		{
			$this->psr4['Tests\Support']                  = SUPPORTPATH;
			$this->classmap['CodeIgniter\Log\TestLogger'] = SYSTEMPATH . 'Test/TestLogger.php';
			$this->classmap['CIDatabaseTestCase']         = SYSTEMPATH . 'Test/CIDatabaseTestCase.php';
		}

		$this->psr4     = array_merge($this->corePsr4, $this->psr4);
		$this->classmap = array_merge($this->coreClassmap, $this->classmap);
		$this->files    = array_merge($this->coreFiles, $this->files);
	}
}
