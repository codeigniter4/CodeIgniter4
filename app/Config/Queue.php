<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Queue Configuration file.
 */
class Queue extends BaseConfig
{
	public $defaultGroup    = 'database';
	public $defaultExchange = 'ci_queue_exchange';
	public $defaultQueue    = 'ci_queue';

	public $rabbitmq = [
		'handler'  => 'RabbitMQ',
		'host'     => 'localhost',
		'port'     => 5672,
		'user'     => 'guest',
		'password' => 'guest',
		'vhost'    => '/',
		'do_setup' => true,
	];

	public $database = [
		'handler'          => 'Database',
		'dbGroup'          => 'default',
		'sharedConnection' => true,
		'table'            => 'ci_queue',
	];

	/*
	 * routing key to queue mapping.
	 * routing key is separated by period.
	 *   '*': exactly one word
	 *   '#': words(greedy) or none
	 */
	public $exchangeMap = [
		'ci_queue_exchange' => [
			'#' => 'ci_queue',
		],
	];

	public $maxRetry             = 3;
	public $timeout              = 30;
	public $remainingDoneMessage = 86400;

	public $tests = [
		'handler'          => 'Database',
		'dbGroup'          => 'tests',
		'sharedConnection' => true,
		'table'            => 'ci_queue',
	];
}
