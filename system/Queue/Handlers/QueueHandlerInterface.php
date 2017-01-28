<?php namespace CodeIgniter\Queue\Handlers;

/**
 * Queue handler Interface.
 */
interface QueueHandlerInterface
{
	/**
	 * constructor.
	 *
	 * @param  array $groupConfig
	 * @param  \Codeigniter\Config\Queue $config
	 */
	public function __construct($groupConfig, \Codeigniter\Config\Queue $config);

	/**
	 * send message to queueing system.
	 *
	 * @param  array  $data
	 * @param  string $routingKey
	 * @param  string $exchangeName
	 */
	public function send($data, string $routingKey = '', string $exchangeName = '');

	/**
	 * Fetch message from queueing system.
	 * When there are no message, this method will return (won't wait).
	 *
	 * @param  callable $callback
	 * @param  string   $queueName
	 * @return boolean  whether callback is done or not.
	 */
	public function fetch(callable $callback, string $queueName = '') : bool;

	/**
	 * Receive message from queueing system.
	 * When there are no message, this method will wait.
	 *
	 * @param  callable $callback
	 * @param  string   $queueName
	 * @return boolean  whether callback is done or not.
	 */
	public function receive(callable $callback, string $queueName = '') : bool;
}
