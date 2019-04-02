<?php namespace CodeIgniter\Queue;

use CodeIgniter\Queue\Exceptions\QueueException;

class QueueTest extends \CIUnitTestCase
{
	public function testQueueExceptionForInvalidGroup()
	{
		$this->expectException(QueueException::class);
		new Queue(new \Config\Queue, 'invalidGroupName');
	}
}
