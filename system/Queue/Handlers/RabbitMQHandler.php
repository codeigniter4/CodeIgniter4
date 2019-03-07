<?php namespace CodeIgniter\Queue\Handlers;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exception\AMQPTimeoutException;

/**
 * Queue handler for RabbitMQ.
 */
class RabbitMQHandler implements QueueHandlerInterface
{
	protected $groupConfig;
	protected $config;
	protected $connection;

	/**
	 * constructor.
	 *
	 * @param  array $groupConfig
	 * @param  \Codeigniter\Config\Queue $config
	 */
	public function __construct($groupConfig, \Codeigniter\Config\Queue $config)
	{
		$this->groupConfig = $groupConfig;
		$this->config       = clone $config;
		$this->connection   = new AMQPStreamConnection(
			$this->groupConfig['host'],
			$this->groupConfig['port'],
			$this->groupConfig['user'],
			$this->groupConfig['password'],
			$this->groupConfig['vhost']
		);
		$this->channel = $this->connection->channel();
		if ($this->groupConfig['do_setup'])
		{
			$this->setup();
		}
	}

	/**
	 * Setup queueing system (system-wide).
	 */
	public function setup()
	{
		foreach ($this->config->exchangeMap as $exchangeName => $queues)
		{
			$this->channel->exchange_declare($exchangeName, 'topic', false, true, false);
			foreach ($queues as $routingKey => $queueName)
			{
				$this->channel->queue_declare($queueName, false, true, false, false);
				$this->channel->queue_bind($queueName, $exchangeName, $routingKey);
			}
		}
	}

	/**
	 * close the connection.
	 *
	 * AMQPConnection::__destruct() do close the connection, so we haven't to close it on destructor.
	 */
	public function closeConnection()
	{
		$this->channel->close();
		$this->connection->close();
	}

	/**
	 * send message to queueing system.
	 *
	 * @param  array  $data
	 * @param  string $routingKey
	 * @param  string $exchangeName
	 */
	public function send($data, string $routingKey = '', string $exchangeName = '')
	{
		$this->channel->basic_publish(
			new AMQPMessage(json_encode($data), ['delivery_mode' => 2]),
			$exchangeName != '' ? $exchangeName : $this->config->defaultExchange,
			$routingKey
		);
	}

	/**
	 * Fetch message from queueing system.
	 * When there are no message, this method will return (won't wait).
	 *
	 * @param  callable $callback
	 * @param  string   $queueName
	 * @return boolean  whether callback is done or not.
	 */
	public function fetch(callable $callback, string $queueName = '') : bool
	{
		return $this->consume($callback, $queueName, 0.001);	// timeout 0.001sec: dummy for non-waiting
	}

	/**
	 * Receive message from queueing system.
	 * When there are no message, this method will wait.
	 *
	 * @param  callable $callback
	 * @param  string   $queueName
	 * @return boolean  whether callback is done or not.
	 */
	public function receive(callable $callback, string $queueName = '') : bool
	{
		return $this->consume($callback, $queueName);
	}

	protected function consume(callable $callback, string $queueName = '', $timeout = 0)
	{
		$this->channel->basic_qos(null, 1, null);
		$this->channel->basic_consume(
			$queueName != '' ? $queueName : $this->config->defaultQueue,
			'',
			false,
			false,
			false,
			false,
			function ($msg) use ($callback)
			{
				$callback(json_decode($msg->body));
				$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
			}
		);

		$ret          = false;
		$consumer_tag = null;
		try
		{
			$consumer_tag = $this->channel->wait(null, false, $timeout);
			$ret          = true;
		}
		catch (AMQPTimeoutException $ex)
		{
			// do nothing.
		}

		if ($consumer_tag !== null) {
			$this->channel->basic_cancel($consumer_tag);
		}

		return $ret;
	}
}
