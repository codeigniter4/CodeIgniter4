<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
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
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Commands\Generators;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Deprecated class for the migration
 * creation command.
 *
 * @deprecated Use make:command instead.
 *
 * @codeCoverageIgnore
 */
class MigrateCreate extends BaseCommand
{
	/**
	 * The group the command is lumped under
	 * when listing commands.
	 *
	 * @var string
	 */
	protected $group = 'Generators';

	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'migrate:create';

	/**
	 * The Command's short description
	 *
	 * @var string
	 */
	protected $description = '[DEPRECATED] Creates a new migration file. Please use "make:migration" instead.';

	/**
	 * The Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'migrate:create <name> [options]';

	/**
	 * The Command's arguments.
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The migration file name.',
	];

	/**
	 * The Command's options.
	 *
	 * @var array
	 */
	protected $options = [
		'-n'      => 'Set root namespace. Defaults to APP_NAMESPACE',
		'--force' => 'Force overwrite existing files.',
	];

	/**
	 * Actually execute a command.
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		// Resolve arguments before passing to make:migration
		$params[0]   = $params[0] ?? CLI::getSegment(2);
		$params['n'] = $params['n'] ?? CLI::getOption('n') ?? APP_NAMESPACE;

		if (array_key_exists('force', $params) || CLI::getOption('force'))
		{
			$params['force'] = null;
		}

		$this->call('make:migration', $params);
	}
}
