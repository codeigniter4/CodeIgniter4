<?php namespace CodeIgniter\Commands\Server;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Launch the PHP development server
 *
 * Not testable, as it throws phpunit for a loop :-/
 * @codeCoverageIgnore
 */
class Serve extends BaseCommand
{
	protected $minPHPVersion = '7.1';

	protected $group = 'CodeIgniter';
	protected $name = 'serve';
	protected $description = 'Launchs the CodeIgniter PHP-Development Server.';
	protected $usage = 'serve';
	protected $arguments = [];
	protected $options = [
		'-php'	 => 'The PHP Binary [default: "PHP_BINARY"]',
		'-host'	 => 'The HTTP Host [default: "localhost"]',
		'-port'	 => 'The HTTP Host Port [default: "8080"]',
	];

	public function run(array $params)
	{
		// Valid PHP Version?
		if (phpversion() < $this->minPHPVersion)
		{
			die("You PHP version must be {$this->minPHPVersion} or higher to run CodeIgniter. Current version: ". phpversion());
		}

		// Collect any user-supplied options and apply them
		$php = CLI::getOption('php') ?? PHP_BINARY;
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
