<?php namespace CodeIgniter\Commands\Server;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Serve extends BaseCommand
{
	protected $group       = 'CodeIgniter';
	protected $name        = 'serve';
	protected $description = 'Launchs the CodeIgniter PHP-Development Server.';
	protected $usage       = 'serve';
	protected $arguments   = [];
	protected $options     = [
		'-php'  => 'The PHP Binary [default: "PHP_BINARY"]',
		'-host' => 'The HTTP Host [default: "localhost"]',
		'-port' => 'The HTTP Host Port [default: "8080"]',
	];

	public function run(array $params)
	{
		// Collect any user-supplied options and apply them
		$php  = CLI::getOption('php') ?? PHP_BINARY;
		$host = CLI::getOption('host') ?? 'localhost';
		$port = CLI::getOption('port') ?? '8080';

		// Get the party started
		CLI::write("CodeIgniter development server started on http://{$host}:{$port}", 'green');
		CLI::write('Press Control-C to stop.');

		// Set the Front Controller path as Document Root
		$docroot = FCPATH;

		// Mimic Apache's mod_rewrite functionality with user settings
		$rewrite = __DIR__ . '/rewrite.php';

		// Call PHP's built-in webserver, making sure to set our
		// base path to the public folder, and to use the rewrite file
		// to ensure our environment is set and it simulates basic mod_rewrite.
		passthru("{$php} -S {$host}:{$port} -t {$docroot} {$rewrite}");
	}
}
