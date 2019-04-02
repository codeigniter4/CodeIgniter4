<?php namespace CodeIgniter\Queue\Handlers;

use CodeIgniter\Queue\Exceptions\QueueException;

/**
 * Queue handler for database.
 */
class DatabaseHandler extends BaseHandler
{
	const STATUS_WAITING   = 10;
	const STATUS_EXECUTING = 20;
	const STATUS_DONE      = 30;
	const STATUS_FAILED    = 40;

	/**
	 * @var string
	 */
	protected $table;

	/**
	 * @var string
	 */
	protected $dbGroup;

	/**
	 * @var boolean
	 */
	protected $sharedConnection;

	/**
	 * @var integer
	 */
	protected $timeout;

	/**
	 * @var integer
	 */
	protected $maxRetry;

	/**
	 * @var integer
	 */
	protected $remainingDoneMessage;

	/**
	 * @var \CodeIgniter\Database\BaseConnection
	 */
	protected $db;

	/**
	 * create tables.
	 */
	public static function migrateUp(\CodeIgniter\Database\Forge $forge)
	{
		$forge->addField([
			'id'          => [
				'type'           => 'INTEGER',
				'auto_increment' => true,
			],
			'queue_name'  => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
			],
			'status'      => [ 'type' => 'INTEGER' ],
			'weight'      => [ 'type' => 'INTEGER' ],
			'retry_count' => [ 'type' => 'INTEGER' ],
			'exec_after'  => [ 'type' => 'DATETIME' ],
			'data'        => [ 'type' => 'TEXT' ],
			'created_at'  => [ 'type' => 'DATETIME' ],
			'updated_at'  => [ 'type' => 'DATETIME' ],
		]);
		$forge->addKey('id', true);
		//      $forge->addKey(['weight', 'id', 'queue_name', 'status', 'exec_after']);
		$forge->createTable('ci_queue', true);
	}

	/**
	 * drop tables.
	 */
	public static function migrateDown(\CodeIgniter\Database\Forge $forge)
	{
		$forge->dropTable('ci_queue');
	}

	/**
	 * constructor.
	 *
	 * @param array         $groupConfig
	 * @param \Config\Queue $config
	 */
	public function __construct($groupConfig, $config)
	{
		parent::__construct($groupConfig, $config);

		$this->table            = $groupConfig['table'];
		$this->dbGroup          = $groupConfig['dbGroup'];
		$this->sharedConnection = $groupConfig['sharedConnection'];

		$this->timeout              = $config->timeout;
		$this->maxRetry             = $config->maxRetry;
		$this->remainingDoneMessage = $config->remainingDoneMessage;

		$this->db = \Config\Database::connect($this->dbGroup, $this->sharedConnection);
	}

	/**
	 * send message to queueing system.
	 *
	 * @param array  $data
	 * @param string $routingKey
	 * @param string $exchangeName
	 */
	public function send($data, string $routingKey = '', string $exchangeName = '')
	{
		if ($exchangeName === '')
		{
			$exchangeName = $this->defaultExchange;
		}
		if (! isset($this->exchangeMap[$exchangeName]))
		{
			throw QueueException::forInvalidExchangeName($exchangeName . ' is not a valid exchange name.');
		}

		$this->db->transStart();
		foreach ($this->exchangeMap[$exchangeName] as $routing => $queueName)
		{
			if ($this->isMatchedRouting($routingKey, $routing))
			{
				$datetime = date('Y-m-d H:i:s');
				$this->db->table($this->table)->insert([
					'queue_name'  => $queueName,
					'status'      => self::STATUS_WAITING,
					'weight'      => 100,
					'retry_count' => 0,
					'exec_after'  => '1753-01-01 00:00:00',
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
		$query = $this->db->table($this->table)
			->where('queue_name', $queueName !== '' ? $queueName : $this->defaultQueue)
			->where('status', self::STATUS_WAITING)
			->where('exec_after <', date('Y-m-d H:i:s'))
			->orderBy('weight')
			->orderBy('id')
			->limit(1)
			->get();
		if (! $query)
		{
			throw QueueException::forFailGetQueueDatabase($this->table);
		}

		$row = $query->getRow();
		if ($row)
		{
			$this->db->table($this->table)
				->where('id', (int) $row->id)
				->where('status', (int)self::STATUS_WAITING)
				->update(['status' => self::STATUS_EXECUTING, 'updated_at' => date('Y-m-d H:i:s')]);
			if ($this->db->affectedRows() > 0)
			{
				$callback(json_decode($row->data));
				$this->db->table($this->table)
					->where('id', $row->id)
					->update(['status' => self::STATUS_DONE, 'updated_at' => date('Y-m-d H:i:s')]);
				return true;
			}
		}
		return false;
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
		while (! $this->fetch($callback, $queueName))
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
		$this->db->table($this->table)
			->set('retry_count', 'retry_count + 1', false)
			->set('status', self::STATUS_WAITING)
			->set('updated_at', date('Y-m-d H:i:s'))
			->where('status', self::STATUS_EXECUTING)
			->where('updated_at <', date('Y-m-d H:i:s', time() - $this->timeout))
			->where('retry_count <', $this->maxRetry)
			->update();
		$this->db->table($this->table)
			->set('retry_count', 'retry_count + 1', false)
			->set('status', self::STATUS_FAILED)
			->set('updated_at', date('Y-m-d H:i:s'))
			->where('status', self::STATUS_EXECUTING)
			->where('updated_at <', date('Y-m-d H:i:s', time() - $this->timeout))
			->where('retry_count >=', $this->maxRetry)
			->update();
		$this->db->table($this->table)
			->where('status', self::STATUS_DONE)
			->where('updated_at <', date('Y-m-d H:i:s', time() - $this->remainingDoneMessage))
			->delete();
	}

	protected function isMatchedRouting($routingKey, $routing): bool
	{
		// to avoid phpcs error(infinite loop), use one-time variant.
		$from = [
			'\*',
			'#',
		];
		$to   = [
			'[-_a-zA-Z0-9]+',
			'.*',
		];

		$regex = str_replace($from, $to, preg_quote($routing, '/'));
		return (bool) preg_match('/^' . $regex . '$/', $routingKey);
	}
}
