<?php namespace CodeIgniter\Log;

/**
 * The CodeIgntier Logger
 *
 * The message MUST be a string or object implementing __toString().
 *
 * The message MAY contain placeholders in the form: {foo} where foo
 * will be replaced by the context data in key "foo".
 *
 * The context array can contain arbitrary data, the only assumption that
 * can be made by implementors is that if an Exception instance is given
 * to produce a stack trace, it MUST be in a key named "exception".
 *
 * @package CodeIgniter\Log
 */
class Logger implements LoggerInterface
{

	/**
	 * Path to save log files to.
	 *
	 * @var string
	 */
	protected $logPath;

	/**
	 * Used by the logThreshold config setting to define
	 * which errors to show.
	 *
	 * @var array
	 */
	protected $logLevels = [
		'emergency' => 1,
		'alert'     => 2,
		'critical'  => 3,
		'error'     => 4,
		'warning'   => 5,
		'notice'    => 6,
		'info'      => 7,
		'debug'     => 8,
	];

	/**
	 * Array of levels to be logged.
	 * The rest will be ignored.
	 * Set in config/logger.php
	 *
	 * @var array
	 */
	protected $loggableLevels = [];

	/**
	 * File permissions
	 *
	 * @var int
	 */
	protected $filePermissions = 0644;

	/**
	 * Format of the timestamp for log files.
	 *
	 * @var string
	 */
	protected $dateFormat = 'Y-m-d H:i:s';

	/**
	 * Filename Extension
	 *
	 * @var string
	 */
	protected $fileExt;

	//--------------------------------------------------------------------

	public function __construct(\App\Config\LoggerConfig $config)
	{
		$this->logPath = ! empty($config->path) ? rtrim($config->path).'/' : WRITEPATH.'logs/';

		$this->loggableLevels = is_array($config->threshold) ? $config->threshold : range(0, (int)$config->threshold);

		$this->fileExt = ! empty($config->fileExtension) ? ltrim($config->fileExtension, '.') : 'php';

		$this->dateFormat = ! empty($config->dateFormat) ?? $this->dateFormat;

		$this->filePermissions = ! empty($config->filePermissions) && is_int($config->filePermissions)
			? $config->filePermissions : $this->filePermissions;
	}

	//--------------------------------------------------------------------

	/**
	 * System is unusable.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function emergency($message, array $context = [])
	{
		$this->log('emergency', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Action must be taken immediately.
	 *
	 * Example: Entire website down, database unavailable, etc. This should
	 * trigger the SMS alerts and wake you up.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function alert($message, array $context = [])
	{
		$this->log('alert', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Critical conditions.
	 *
	 * Example: Application component unavailable, unexpected exception.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function critical($message, array $context = [])
	{
		$this->log('critical', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function error($message, array $context = [])
	{
		$this->log('error', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Exceptional occurrences that are not errors.
	 *
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things
	 * that are not necessarily wrong.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function warning($message, array $context = [])
	{
		$this->log('warning', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Normal but significant events.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function notice($message, array $context = [])
	{
		$this->log('notice', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Interesting events.
	 *
	 * Example: User logs in, SQL logs.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function info($message, array $context = [])
	{
		$this->log('info', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Detailed debug information.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function debug($message, array $context = [])
	{
		$this->log('debug', $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed  $level
	 * @param string $message
	 * @param array  $context
	 *
	 * @return bool
	 */
	public function log(string $level, $message, array $context = []): bool
	{
		if ( ! in_array($level, $this->loggableLevels))
		{
			return false;
		}

		// Parse our placeholders
		$message = $this->interpolate($message, $context);

		$filepath = $this->logPath.'log-'.date('Y-m-d').'.'.$this->fileExt;

		$msg = '';

		if ( ! file_exists($filepath))
		{
			$newfile = true;

			// Only add protection to php files
			if ($this->fileExt === 'php')
			{
				$msg .= "<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>\n\n";
			}
		}

		if ( ! $fp = @fopen($filepath, 'ab'))
		{
			return false;
		}

		// Instantiating DateTime with microseconds appended to initial date is needed for proper support of this format
		if (strpos($this->dateFormat, 'u') !== false)
		{
			$microtime_full  = microtime(true);
			$microtime_short = sprintf("%06d", ($microtime_full - floor($microtime_full)) * 1000000);
			$date            = new DateTime(date('Y-m-d H:i:s.'.$microtime_short, $microtime_full));
			$date            = $date->format($this->dateFormat);
		}
		else
		{
			$date = date($this->dateFormat);
		}

		$msg .= strtoupper($level).' - '.$date.' --> '.$message."\n";

		flock($fp, LOCK_EX);

		for ($written = 0, $length = strlen($msg); $written < $length; $written += $result)
		{
			if (($result = fwrite($fp, substr($msg, $written))) === false)
			{
				break;
			}
		}

		flock($fp, LOCK_UN);
		fclose($fp);

		if (isset($newfile) && $newfile === true)
		{
			chmod($filepath, $this->filePermissions);
		}

		return is_int($result);
	}

	//--------------------------------------------------------------------

	/**
	 * Replaces any placeholders in the message with variables
	 * from the context, as well as a few special items like:
	 *
	 * {session_vars}
	 * {post_vars}
	 * {get_vars}
	 *
	 * @param       $message
	 * @param array $context
	 *
	 * @return string
	 */
	protected function interpolate($message, array $context = [])
	{
		// build a replacement array with braces around the context keys
		$replace = [];

		foreach ($context as $key => $val)
		{
			// Verify that the 'exception' key is actually an exception
			// or error, both of which implement the 'Throwable' interface.
			if ($key == 'exception' && $val instanceof \Throwable)
			{
				$val = $val->getMessage().' '.$val->getFile().':'. $val->getLine();
			}

			// todo - sanitize input before writing to file?
			$replace['{'.$key.'}'] = $val;
		}

		// Add special placeholders
		$replace['{post_vars}'] = '$_POST: '.print_r($_POST, true);
		$replace['{get_vars}']  = '$_GET: '.print_r($_GET, true);

		if (isset($_SESSION))
		{
			$replace['{session_vars}'] = '$_SESSION: '.print_r($_SESSION, true);
		}

		// interpolate replacement values into the message and return
		return strtr($message, $replace);
	}

	//--------------------------------------------------------------------
}
