<?php namespace CodeIgniter\Queue;

use CodeIgniter\Queue\Exceptions\QueueException;

/**
 * Queue class.
 *
 * Queueing system has several actors, but this class has all actions.
 *
 * "Queue Producers" send messages to "Exchange".
 * "Exchange" deliver messages to queues based on routing-setting.
 *
 * "Queue Customers" recieve each message from queueing system.
 * There can be many customers, but each message is processed just once.
 * Message is deleted when process is completed.
 * When incompleted, message is returnd to queueing system.
 */
class Queue implements QueueInterface
{
	/**
	 * Config object.
	 *
	 * @var \Config\Queue
	 */
	protected $config;

	/**
	 * Config of the connection group to use
	 *
	 * @var array
	 */
	protected $groupConfig;

	/**
	 * Constructor.
	 *
	 * @param \Config\Queue $config
	 * @param string|array  $group  The name of the connection group to use,
	 *                               or an array of configuration settings.
	 */
	public function __construct($config, $group = '')
	{
		if (is_array($group))
		{
			$groupConfig = $group;
			$group       = 'custom';
		}
		else
		{
			if ($group === '')
			{
				$group = ENVIRONMENT === 'testing' ? 'tests' : (string) $config->defaultGroup;
			}

			if (isset($config->$group))
			{
				$groupConfig = $config->$group;
			}
			else
			{
				throw QueueException::forInvalidGroup($group);
			}
		}

		$this->groupConfig = $groupConfig;
		$this->config      = $config;
	}

	/**
	 * connecting queueing system.
	 *
	 * @return CodeIgniter\Queue\Handlers\BaseHandler
	 */
	public function connect()
	{
		$handler = '\\CodeIgniter\\Queue\\Handlers\\' . $this->groupConfig['handler'] . 'Handler';
		return new $handler($this->groupConfig, $this->config);
	}
}
