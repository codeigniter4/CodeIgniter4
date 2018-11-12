<?php
namespace Tests\Support\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CodeIgniter;

class AppInfo extends BaseCommand
{

	protected $group       = 'demo';
	protected $name        = 'app:info';
	protected $description = 'Displays basic application information.';

	public function run(array $params)
	{
		CLI::write('CI Version: ' . CLI::color(CodeIgniter::CI_VERSION, 'red'));
	}

}
