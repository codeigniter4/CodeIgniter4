<?php namespace CodeIgniter\Log\Handlers;

abstract class BaseHandler implements HandlerInterface
{

	/**
	 * @var array
	 */
	protected $handles;

	/**
	 * @var string
	 */
	protected $dateFormat = 'Y-m-d H:i:s';

	//--------------------------------------------------------------------

	public function __construct(array $config)
	{
	    $this->handles = $config['handles'] ?? [];
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
	public function canHandle(int $level): bool
	{
		return in_array($level, $this->handles);
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
	abstract public function handle($level, $message): bool;

	//--------------------------------------------------------------------

	/**
	 * Stores the date format to use while logging messages.
	 *
	 * @param string $format
	 *
	 * @return HandlerInterface
	 */
	public function setDateFormat(string $format): HandlerInterface
	{
	    $this->dateFormat = $format;

		return $this;
	}

	//--------------------------------------------------------------------


}