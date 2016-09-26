<?php namespace CodeIgniter\Log\Handlers;

/**
 * Class TestHandler
 *
 * A simple LogHandler that stores the logs in memory.
 * Only used for testing purposes.
 */
class TestHandler implements HandlerInterface
{
	/**
	 * @var array
	 */
	protected $handles;

	/**
	 * @var string
	 */
	protected $dateFormat = 'Y-m-d H:i:s';

	/**
	 * Local storage for logs.
	 * @var array
	 */
	protected static $logs = [];

	//--------------------------------------------------------------------

	public function __construct(array $config)
	{
		$this->handles = $config['handles'] ?? [];

		self::$logs = [];
	}

	//--------------------------------------------------------------------

	/**
	 * Checks whether the Handler will handle logging items of this
	 * log Level.
	 *
	 * @param $level
	 *
	 * @return bool
	 */
	public function canHandle(string $level): bool
	{
		return in_array($level, $this->handles);
	}

	//--------------------------------------------------------------------

	/**
	 * Stores the date format to use while logging messages.
	 *
	 * @param string $format
	 *
	 * @return HandlerInterface
	 */
	public function setDateFormat(string $format)
	{
		$this->dateFormat = $format;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Handles logging the message.
	 * If the handler returns false, then execution of handlers
	 * will stop. Any handlers that have not run, yet, will not
	 * be run.
	 *
	 * @param $level
	 * @param $message
	 *
	 * @return bool
	 */
	public function handle($level, $message): bool
	{
		$date = date($this->dateFormat);

		self::$logs[] = strtoupper($level).' - '.$date.' --> '.$message;

		return true;
	}

	//--------------------------------------------------------------------

	public static function getLogs()
	{
	    return self::$logs;
	}

	//--------------------------------------------------------------------


}