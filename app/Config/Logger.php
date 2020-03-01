<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * The logging system supports "logging" messages using multiple communication technologies.
 * Currently there are two handlers - File and ChromeLogger.
 */
class Logger extends BaseConfig
{
	// *******************************************************************************
	//                           File Logging Settings
	// *******************************************************************************

	/**
	 * The $fileLevelsHandled property defines the message severity levels that
	 * should be written to the log file.
	 *
	 * Here are the values and the message type
	 *     0 - off
	 *     1 - 'emergency'
	 *     2 - 'alert'
	 *     3 - 'critical'
	 *     4 - 'error'
	 *     5 - 'warning'
	 *     6 - 'notice'
	 *     7 - 'info'
	 *     8 - 'debug'
	 *
	 * Setting a value of 0 (zero) turns the file logging off.
	 * You can enable logging by setting a value in the range 1 to 8.
	 * Not setting a value (leaving it blank) will cause a LogException with
	 * the message - 'null' is an invalid log level.
	 *
	 * If a single value is supplied all severity levels less than or equal to the value
	 * will be logged. In other words, setting
	 *     $levelsHandled = 3;
	 * would mean that critical, alert, and emergency messages would be logged.
	 *
	 * You can also pass an array of levels to create a mix of message types.
	 * For instance,
	 *     $levelsHandled = [1, 3, 8];
	 * would result in emergency, alert, and debug messages being logged.
	 *
	 * There is no meaning to the order of the values in the array.
	 * If you put a zero in an array the effect is same as a single int value of 0 which
	 * turns file logging off.
	 *
	 * If you put a single value in an array then only that level will be logged.
	 * For instance, using
	 *     $levelsHandled = [8];
	 * then only 'debug' messages get logged.
	 *
	 * For a live site you'll usually want critical (3) or lower to be logged, otherwise
	 * your log files will fill up very fast.
	 *
	 * @var integer|array
	 */
	public $fileLevelsHandled = 8;

	/**
	 * $logsDir
	 *
	 * The absolute path to folder where logs will be written.
	 * Use a full getServer path with trailing slash.
	 * 
	 * Leave $logsDir BLANK and get the default 'WRITEPATH/logs/'
	 *
	 * @var string
	 */
	public $logsDir;

	/**
	 * $fileName
	 *
	 * Really just the text to prefix log file names
	 *
	 * Log file names are composed of three parts
	 *     1. $fileName (defaults to 'CI_')
	 *     2. The date (in Y-m-d format)
	 *     3. $fileExtension (defaults to 'log')
	 *
	 * For example, put those together and you get CI_2020-02-23.log
	 *
	 * Leave $fileName BLANK and get the default 'CI_'
	 *
	 * @var string
	 */
	public $fileName;

	/**
	 * The file extension for log files.
	 *
	 * Be absolutely certain you are NOT saving log files to
	 * a directory that is publicly accessible!
	 *
	 * Leave $fileExtension BLANK and get the default 'log'.
	 *
	 * @var string
	 */
	public $fileExtension;

	/**
	 * The file system permissions to be applied on newly created log files.
	 *
	 * IMPORTANT: This MUST be an integer (no quotes) and you MUST use octal
	 * notation (i.e. 0700, 0644, etc.)
	 *
	 * Leave $filePermissions BLANK and get the default 0664.
	 *
	 * @var octal
	 */
	public $filePermissions;

	// *******************************************************************************
	//                            Chrome Logger Settings
	// *******************************************************************************

	/**
	 * Chrome Logger is a Google Chrome extension for server side logging and debugging
	 * in chrome console.
	 * https://chrome.google.com/webstore/detail/chrome-logger/noaneddfkdjfnfdakjjmocngnfkfehhd
	 *
	 * Sending potentially sensitive data from a production environment is a very bad idea,
	 * Therefore, CodeIgniter's interface with Chrome Logger is only allowed to run when
	 * $enableChromeLogger === true AND ENVIRONMENT === 'development'
	 *
	 * And of course, you need to set a value for the $chromeLoggerlevelsHandled property.
	 * That property is distributed with its value set to zero which,
	 * as you know, means it is "turned off".
	 */
	public $enableChromeLogger = false;

	/**
	 * $chromeLoggerlevelsHandled serves the same purpose as the $fileLevelsHandled property
	 * in that it defines the message severity levels. In this case it is the levels that
	 * should be sent to a Chrome browser that is using the Chrome Logger extension.
	 *
	 * The extension is for debugging server side applications using the chrome javascript console.
	 *
	 *  The Chrome Console does not use the PSR-3 Logger message types.
	 * For your reference, this table shows how the PSR-3 values map to the
	 * Chrome Console message types. PSR-3 type in parenthesis:
	 *
	 *     0 - off
	 *     1 - 'error' (emergency)
	 *     2 - 'error' (alert)
	 *     3 - 'error' (critical)
	 *     4 - 'error' (error)
	 *     5 - 'warn'  (warning)
	 *     6 - 'warn'  (notice)
	 *     7 - 'info'  (info)
	 *     8 - 'info'  (debug)
	 *
	 * @var integer|array
	 */
	public $chromeLoggerLevelsHandled = 0; // off by default.

	// *******************************************************************************
	//                              Logger Settings
	// *******************************************************************************

	/**
	 * Format for dates. Used for log entries.
	 *
	 * @var string
	 */
	public $dateFormat = 'Y-m-d H:i:s';

	/**
	 * $handlers
	 *
	 * The $handlers property is a list of classes that the Logger can use to "handle"
	 * log requests. Handlers run sequentially in the order defined in the array starting with first item.
	 *
	 * Each `$handlers` item is a key/value pair where the key is the handler class name
	 * and the value is a fully qualified class name constant.
	 */
	public $handlers = [
		'FileHandler'         => \CodeIgniter\Log\Handlers\FileHandler::class,
		'ChromeLoggerHandler' => \CodeIgniter\Log\Handlers\ChromeLoggerHandler::class,
	];

}
