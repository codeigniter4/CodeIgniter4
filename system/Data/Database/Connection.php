<?php namespace CodeIgniter\Data\Database;

abstract class Connection
{
	protected $connectionConfig;
	protected $id;

	abstract public function connect(): bool;
	abstract public function reconnect();
	abstract public function disconnect();
	abstract public function getErrorCode();
	abstract public function getErrorMessage();

	public function __construct(\CodeIgniter\Config\Database\Connection $config)
	{
		$this->connectionConfig = $config;
	}

	public function getConfig(): \CodeIgniter\Config\Database\Connection
	{
		return $this->connectionConfig;
	}

	public function getId()
	{
		return $this->id;
	}
}
