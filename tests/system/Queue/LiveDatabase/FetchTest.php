<?php namespace CodeIgniter\Queue\LiveDatabase;


class FetchTest extends \CodeIgniter\CIQueueDatabaseTestCase
{
	protected $refresh = true;

	public function testFetchNoData()
	{
		$message = '';
		$this->queue->fetch(
			function($data) use ($message)
			{
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
			function($data) use (&$message)
			{
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
			function($data) use (&$message)
			{
				$message = $data;
			}
		);
		
		$this->assertEquals('It’s fine today. It’s fine today.', $message);
	}
}