<?php namespace Config;

/**
 * Queue Configuration file.
 */
class Queue extends \CodeIgniter\Config\Queue
{
	public $tests = [
		'handler'          => 'Database',
		'dbGroup'          => 'tests',
		'sharedConnection' => true,
		'table'            => 'ci_queue',
	];
}
