<?php namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class LoggerConfig extends BaseConfig
{
	/*
	|--------------------------------------------------------------------------
	| Error Logging Threshold
	|--------------------------------------------------------------------------
	|
	| You can enable error logging by setting a threshold over zero. The
	| threshold determines what gets logged. Any values below or equal to the
	| threshold will be logged. Threshold options are:
	|
	|	0 = Disables logging, Error logging TURNED OFF
	|	1 = Emergency Messages  - System is unusable
	|	2 = Alert Messages      - Action Must Be Taken Immediately
	|   3 = Critical Messages   - Application component unavailable, unexpected exception.
	|   4 = Runtime Errors      - Don't need immediate action, but should be monitored.
	|   5 = Warnings            - Exceptional occurrences that are not errors.
	|   6 = Notices             - Normal but significant events.
	|   7 = Info                - Interesting events, like user logging in, etc.
	|   8 = Debug               - Detailed debug information.
	|   9 = All Messages
	|
	| You can also pass an array with threshold levels to show individual error types
	|
	| 	array(1, 2, 3, 8) = Emergency, Alert, Critical, and Debug messages
	|
	| For a live site you'll usually enable Critical or higher (3) to be logged otherwise
	| your log files will fill up very fast.
	|
	*/
	public $threshold = 0;

	/*
	|--------------------------------------------------------------------------
	| Error Logging Directory Path
	|--------------------------------------------------------------------------
	|
	| Leave this BLANK unless you would like to set something other than the default
	| application/logs/ directory. Use a full server path with trailing slash.
	|
	*/
	public $path = '';

	/*
	|--------------------------------------------------------------------------
	| Log File Extension
	|--------------------------------------------------------------------------
	|
	| The default filename extension for log files. The default 'php' allows for
	| protecting the log files via basic scripting, when they are to be stored
	| under a publicly accessible directory.
	|
	| Note: Leaving it blank will default to 'php'.
	|
	*/
	public $fileExtension = '';

	/*
	|--------------------------------------------------------------------------
	| Log File Permissions
	|--------------------------------------------------------------------------
	|
	| The file system permissions to be applied on newly created log files.
	|
	| IMPORTANT: This MUST be an integer (no quotes) and you MUST use octal
	|            integer notation (i.e. 0700, 0644, etc.)
	*/
	public $filePermissions = 0644;

	/*
	|--------------------------------------------------------------------------
	| Date Format for Logs
	|--------------------------------------------------------------------------
	|
	| Each item that is logged has an associated date. You can use PHP date
	| codes to set your own date formatting
	|
	*/
	public $dateFormat = 'Y-m-d H:i:s';
}
