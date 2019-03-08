<?php namespace CodeIgniter\Queue\Live;

use CodeIgniter\Test\CIDatabaseTestCase;

class FetchTest extends CIDatabaseTestCase
{
	protected $refresh  = true;
	protected $basePath = SUPPORTPATH . 'Queue';

	public function setUp()
	{
		parent::setUp();
		$this->queue = \CodeIgniter\Config\Services::queue(false);
	}

	public function tearDown()
	{
		$this->db->table('ci_queue')->truncate();
	}

	//--------------------------------------------------------------------

	public function testFetchNoData()
	{
		$message = '';
		$this->queue->fetch(
			function ($data) use ($message) {
				$message = $data;
			}
		);

		$this->assertEquals('', $message);
	}

	//--------------------------------------------------------------------

	public function testFetch()
	{
		$this->queue->send('It’s fine today');

		$message = '';
		$this->queue->fetch(
			function ($data) use (&$message) {
				$message = $data;
			}
		);

		$this->assertEquals('It’s fine today', $message);
	}

	//--------------------------------------------------------------------

	public function testReceive()
	{
		$this->queue->send('It’s fine today. It’s fine today.');

		$message = '';
		$this->queue->receive(
			function ($data) use (&$message) {
				$message = $data;
			}
		);

		$this->assertEquals('It’s fine today. It’s fine today.', $message);
	}
}
