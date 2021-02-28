<?php
namespace Tests\Support\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CodeIgniter;

class InvalidCommand extends BaseCommand
{

	protected $group       = 'demo';
	protected $name        = 'app:invalid';
	protected $description = '';

	public function __construct()
	{
		throw new \ReflectionException();
	}

	public function run(array $params)
	{
		CLI::write('CI Version: ' . CLI::color(CodeIgniter::CI_VERSION, 'red'));
	}
}
