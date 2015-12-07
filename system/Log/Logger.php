<?php namespace CodeIgniter\Log;

use App\Config\LoggerConfig;
use CodeIgniter\Log\Handlers\HandlerInterface;
use Psr\Log\LoggerInterface;

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

	/**
	 * Caches instances of the handlers.
	 *
	 * @var array
	 */
	protected $handlers = [];

	/**
	 * Holds the configuration for each handler.
	 * The key is the handler's class name. The
	 * value is an associative array of configuration
	 * items.
	 *
	 * @var array
	 */
	protected $handlerConfig = [];

	//--------------------------------------------------------------------

	public function __construct(LoggerConfig $config)
	{
		$this->loggableLevels = is_array($config->threshold) ? $config->threshold : range(0, (int)$config->threshold);

		$this->dateFormat = ! empty($config->dateFormat) ?? $this->dateFormat;

		if (! is_array($config->handlers) || empty($config->handlers))
		{
			throw new \RuntimeException('LoggerConfig must provide at least one Handler.');
		}

		// Save the handler configuration for later.
		// Instances will be created on demand.
		$this->handlerConfig = $config->handlers;
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
	public function log(\string $level, $message, array $context = []): bool
	{
		$level = strtolower($level);

		// Is the level a valid level?
		if (! array_key_exists($level, $this->logLevels))
		{
			throw new \InvalidArgumentException($level.' is an invalid log level.');
		}

		// Does the app want to log this right now?
		if ( ! in_array($level, $this->loggableLevels))
		{
			return false;
		}

		// Parse our placeholders
		$message = $this->interpolate($message, $context);

		foreach ($this->handlerConfig as $className => $config)
		{
			if (! $className instanceof HandlerInterface)
			{
				continue;
			}

			/**
			 * @var \CodeIgniter\Log\Handlers\HandlerInterface
			 */
			$handler = new $className($config);

			if (! $handler->canHandle($level))
			{
				continue;
			}

			// If the handler returns false, then we
			// don't execute any other handlers.
			if (! $handler->setDateFormat($this->dateFormat)->handle($level, $message))
			{
				break;
			}
		}

		return false;
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

	/**
	 * Acts as a factory for Handlers so we only load them if we need them.
	 *
	 * @param string $name  The class name of the Handler to get.
	 */
	protected function getHandler(string $name): Hand
	{

	}

	//--------------------------------------------------------------------

}
