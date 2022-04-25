<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Config;

use Laminas\Escaper\Escaper;
use Laminas\Escaper\Exception\ExceptionInterface;
use Laminas\Escaper\Exception\InvalidArgumentException as EscaperInvalidArgumentException;
use Laminas\Escaper\Exception\RuntimeException;
use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

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
        AbstractLogger::class                  => SYSTEMPATH . 'ThirdParty/PSR/Log/AbstractLogger.php',
        InvalidArgumentException::class        => SYSTEMPATH . 'ThirdParty/PSR/Log/InvalidArgumentException.php',
        LoggerAwareInterface::class            => SYSTEMPATH . 'ThirdParty/PSR/Log/LoggerAwareInterface.php',
        LoggerAwareTrait::class                => SYSTEMPATH . 'ThirdParty/PSR/Log/LoggerAwareTrait.php',
        LoggerInterface::class                 => SYSTEMPATH . 'ThirdParty/PSR/Log/LoggerInterface.php',
        LoggerTrait::class                     => SYSTEMPATH . 'ThirdParty/PSR/Log/LoggerTrait.php',
        LogLevel::class                        => SYSTEMPATH . 'ThirdParty/PSR/Log/LogLevel.php',
        NullLogger::class                      => SYSTEMPATH . 'ThirdParty/PSR/Log/NullLogger.php',
        ExceptionInterface::class              => SYSTEMPATH . 'ThirdParty/Escaper/Exception/ExceptionInterface.php',
        EscaperInvalidArgumentException::class => SYSTEMPATH . 'ThirdParty/Escaper/Exception/InvalidArgumentException.php',
        RuntimeException::class                => SYSTEMPATH . 'ThirdParty/Escaper/Exception/RuntimeException.php',
        Escaper::class                         => SYSTEMPATH . 'ThirdParty/Escaper/Escaper.php',
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
        if (isset($_SERVER['CI_ENVIRONMENT']) && $_SERVER['CI_ENVIRONMENT'] === 'testing') {
            $this->psr4['Tests\Support']                  = SUPPORTPATH;
            $this->classmap['CodeIgniter\Log\TestLogger'] = SYSTEMPATH . 'Test/TestLogger.php';
            $this->classmap['CIDatabaseTestCase']         = SYSTEMPATH . 'Test/CIDatabaseTestCase.php';
        }

        $this->psr4     = array_merge($this->corePsr4, $this->psr4);
        $this->classmap = array_merge($this->coreClassmap, $this->classmap);
        $this->files    = [...$this->coreFiles, ...$this->files];
    }
}
