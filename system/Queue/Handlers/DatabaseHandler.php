<?php namespace CodeIgniter\Queue\Handlers;

/**
 * Queue handler for database.
 */
class DatabaseHandler implements QueueHandlerInterface
{
	const STATUS_WAITING   = 10;
	const STATUS_EXECUTING = 20;
	const STATUS_DONE      = 30;
	const STATUS_FAILED    = 40;
	protected $group_config;
	protected $config;
	protected $db;

	/**
	 * constructor.
	 *
	 * @param  array $group_config
	 * @param  \Codeigniter\Config\Queue $config
	 */
	public function __construct($group_config, \Codeigniter\Config\Queue $config)
	{
		$this->group_config = $group_config;
		$this->config       = clone $config;
		$this->db           = \Config\Database::connect($this->group_config['dbGroup'], $this->group_config['sharedConnection']);
	}

	/**
	 * Setup queueing system (system-wide).
	 */
	/*
	public function setup()
	{
		CREATE TABLE ci_queue (
			id          int(11) NOT NULL AUTO_INCREMENT,
			queue_name  varchar(255) NOT NULL,
			status      int(11) NOT NULL,
			weight      int(11) NOT NULL,
			retry_count int(11) NOT NULL DEFAULT 0,
			exec_after  datetime NOT NULL,
			data        text NOT NULL,
			created_at  datetime NOT NULL,
			updated_at  datetime NOT NULL,
			PRIMARY KEY (id),
			INDEX idx_queue_fetch(weight, id, queue_name, status, exec_after)
		) ENGINE=InnoDB;
	}
	*/

	/**
	 * send message to queueing system.
	 *
	 * @param  array  $data
	 * @param  string $routingKey
	 * @param  string $exchangeName
	 */
	public function send($data, string $routingKey = '', string $exchangeName = '')
	{
		if ($exchangeName == '') {
			$exchangeName = $this->config->defaultExchange;
		}
		if ( ! isset($this->config->exchange_map[$exchangeName]))
		{
			throw new InvalidArgumentException($exchangeName.' is not a valid exchange name');
		}

		$this->db->transStart();
		foreach ($this->config->exchange_map[$exchangeName] as $routing => $queueName)
		{
			if ($this->isMatchedRouting($routingKey, $routing))
			{
				$datetime = date('Y-m-d H:i:s');
				$this->db->table($this->group_config['table'])->insert([
					'queue_name'  => $queueName,
					'status'      => self::STATUS_WAITING,
					'weight'      => 100,
					'retry_count' => 0,
					'exec_after'  => '0000-00-00 00:00:00',
					'data'        => json_encode($data),
					'created_at'  => $datetime,
					'updated_at'  => $datetime,
				]);
			}
		}
		$this->db->transComplete();
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
		$row = $this->db->table($this->group_config['table'])
			->where('queue_name', $queueName != '' ? $queueName : $this->config->defaultQueue)
			->where('status', self::STATUS_WAITING)
			->where('exec_after <', date('Y-m-d H:i:s'))
			->orderBy('weight')
			->orderBy('id')
			->limit(1)
			->get()->getRow();
		if ($row)
		{
			$this->db->table($this->group_config['table'])
				->where('id', (int) $row->id)
				//->where('status', (int)self::STATUS_WAITING)	// important: multiple customers will try to get this record
				->where('status = '.self::STATUS_WAITING)	// avoid a bug when using same column at a time...
				->update(['status' => self::STATUS_EXECUTING, 'updated_at' => date('Y-m-d H:i:s')]);
			if ($this->db->affectedRows() > 0)
			{
				$callback(json_decode($row->data));
				$this->db->table($this->group_config['table'])
					->where('id', $row->id)
					->update(['status' => self::STATUS_DONE, 'updated_at' => date('Y-m-d H:i:s')]);
				return true;
			}
		}
		return false;
	}

	/**
	 * Recieve message from queueing system.
	 * When there are no message, this method will wait.
	 *
	 * @param  callable $callback
	 * @param  string   $queueName
	 * @return boolean  whether callback is done or not.
	 */
	public function recieve(callable $callback, string $queueName = '') : bool
	{
		while( ! $this->fetch($callback, $queueName))
		{
			usleep(1000000);
		}
		return true;
	}

	/**
	 * housekeeper.
	 */
	public function keepHouse()
	{
		$this->db->table($this->group_config['table'])
			//->set('retry_count', 'retry_count + 1', false)  // bug... value will be escaped
			->set('retry_count', 3, false)
			->set('status', self::STATUS_WAITING)
			->set('updated_at', date('Y-m-d H:i:s'))
			->where('status = '.self::STATUS_EXECUTING)	// avoid a bug when using same column at a time...
			->where('updated_at <', date('Y-m-d H:i:s', time() - 30))
			->where('retry_count <', 3)
			->update();
		$this->db->table($this->group_config['table'])
			->set('retry_count', 'retry_count + 1', false)
			->set('status', self::STATUS_FAILED)
			->set('updated_at', date('Y-m-d H:i:s'))
			->where('status = '.self::STATUS_EXECUTING)	// avoid a bug when using same column at a time...
			->where('updated_at <', date('Y-m-d H:i:s', time() - 30))
			->where('retry_count >=', 3)
			->update();
		$this->db->table($this->group_config['table'])
			->where('status', self::STATUS_DONE)
			->where('updated_at <', date('Y-m-d H:i:s', time() - 86400))
			->delete();
	}

	protected function isMatchedRouting($routingKey, $routing): bool
	{
		$regex = str_replace(
			['\*',             '#'],
			['[-_a-zA-Z0-9]+', '.*'],
			preg_quote($routing, '/')
		);
		return (bool) preg_match('/^'.$regex.'$/', $routingKey);
	}
}
