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

class CreateScaffold extends BaseCommand
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
	protected $name = 'make:scaffold';

	/**
	 * The Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Creates a complete set of scaffold files.';

	/**
	 * The Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'make:scaffold <name> [options]';

	/**
	 * The Command's arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The class name',
	];

	protected $options = [
		'-base'    => 'Add the \'-base\' option to controller scaffold.',
		'-restful' => 'Add the \'-restful\' option to controller scaffold.',
		'-dbgroup' => 'Add the \'-dbgroup\' option to model and session migration scaffold.',
		'-t'       => 'Add the \'-t\' option for the session migration scaffold.',
		'-n'       => 'Set root namespace. Defaults to APP_NAMESPACE.',
		'-force'   => 'Force overwrite existing files.',
	];

	public function run(array $params)
	{
		// Resolve options
		$base         = $params['base'] ?? CLI::getOption('base');
		$rest         = $params['restful'] ?? CLI::getOption('restful');
		$group        = $params['dbgroup'] ?? CLI::getOption('dbgroup');
		$tableSession = $params['t'] ?? CLI::getOption('t') ?? 'ci_sessions';
		$namespace    = $params['n'] ?? CLI::getOption('n');
		$force        = array_key_exists('force', $params) ?? CLI::getOption('force');

		// Sets additional options
		$genOptions = ['n' => $namespace];
		if ($force)
		{
			$genOptions['force'] = null;
		}

		$controllerOpts = [];
		if ($base)
		{
			$controllerOpts['base'] = $base;
		}
		elseif ($rest)
		{
			$controllerOpts['restful'] = $rest;
		}

		$modelOpts   = [
			'dbgroup' => $group,
			'entity'  => null,
		];
		$sessionOpts = [
			'g' => $group,
			't' => $tableSession,
		];

		// Call those commands!
		$class = $params[0] ?? CLI::getOption(0);
		$this->call('make:controller', array_merge([$class], $controllerOpts, $genOptions));
		$this->call('make:model', array_merge([$class], $modelOpts, $genOptions));
		$this->call('make:entity', array_merge([$class], $genOptions));
		$this->call('migrate:create', array_merge([$class], $genOptions));
		$this->call('make:seeder', array_merge([$class], $genOptions));
		$this->call('session:migration', array_merge([$class], $sessionOpts, $genOptions));
	}
}
