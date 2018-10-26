<?php
namespace Tests\Support\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CodeIgniter;

abstract class AbstractInfo extends BaseCommand
{

	protected $group = 'demo';
	protected $name = 'app:pablo';
	protected $description = 'Displays basic application information.';

}
