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

use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\GeneratorCommand;

/**
 * Creates a migration file for database sessions.
 *
 * @package CodeIgniter\Commands
 */
class CreateSessionMigration extends GeneratorCommand
{
	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'session:migration';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Generates the migration file for database sessions.';

	/**
	 * The Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'session:migration [options]';

	/**
	 * The Command's Options
	 *
	 * @var array
	 */
	protected $options = [
		'-g' => 'Set database group',
		'-t' => 'Set table name',
	];

	/**
	 * {@inheritDoc}
	 */
	protected function getClassName(): string
	{
		$tableName = $this->params['t'] ?? CLI::getOption('t') ?? 'ci_sessions';

		return "Migration_create_{$tableName}_table";
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getNamespacedClass(string $rootNamespace, string $class): string
	{
		return $rootNamespace . '\\Database\\Migrations\\' . $class;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function modifyBasename(string $filename): string
	{
		return str_replace('Migration', gmdate(config('Migrations')->timestampFormat), $filename);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getTemplate(): string
	{
		$data = [
			'DBGroup'   => $this->params['g'] ?? CLI::getOption('g'),
			'tableName' => $this->params['t'] ?? CLI::getOption('t') ?? 'ci_sessions',
			'matchIP'   => config('App')->sessionMatchIP ?? false,
		];

		$template = $this->getGeneratorViewFile('CodeIgniter\\Commands\\Generators\\Views\\session_migration.tpl.php', $data);

		return str_replace('<@php', '<?php', $template);
	}
}
