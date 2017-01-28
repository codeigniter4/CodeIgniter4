<?php namespace CodeIgniter;

use CodeIgniter\Test;

class CIQueueDatabaseTestCase extends Test\CIDatabaseTestCase
{
	protected $basePath = APPPATH.'../tests/_support/_queue_database';

	public function setUp()
	{
		parent::setUp();
		$this->queue = \CodeIgniter\Config\Services::queue(false);
	}

	public function tearDown()
	{
		$this->db->table('ci_queue')->truncate();
	}
}
