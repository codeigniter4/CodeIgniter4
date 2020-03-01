<?php

/**
 * Config for testing CodeIgniter\Log\Logger.
 */

namespace Tests\Support\Log\Config;

class MockLoggerConfig
{
	public $handlers = [
		'FileHandler'         => \CodeIgniter\Log\Handlers\FileHandler::class,
		'ChromeLoggerHandler' => \CodeIgniter\Log\Handlers\ChromeLoggerHandler::class,
	];

	public $fileLevelsHandled = 8;

	public $chromeLoggerLevelsHandled = 0;

	public $dateFormat = 'Y-m-d H:i:s';

	public $enableChromeLogger = true;

}
