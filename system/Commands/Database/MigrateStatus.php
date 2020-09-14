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

namespace CodeIgniter\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

/**
 * Displays a list of all migrations and whether they've been run or not.
 *
 * @package CodeIgniter\Commands
 */
class MigrateStatus extends BaseCommand
{
	/**
	 * The group the command is lumped under
	 * when listing commands.
	 *
	 * @var string
	 */
	protected $group = 'Database';

	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'migrate:status';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Displays a list of all migrations and whether they\'ve been run or not.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'migrate:status [options]';

	/**
	 * the Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [];

	/**
	 * the Command's Options
	 *
	 * @var array
	 */
	protected $options = [
		'-g' => 'Set database group',
	];

	/**
	 * Namespaces to ignore when looking for migrations.
	 *
	 * @var array
	 */
	protected $ignoredNamespaces = [
		'CodeIgniter',
		'Config',
		'Tests\Support',
		'Kint',
		'Laminas\ZendFrameworkBridge',
		'Laminas\Escaper',
		'Psr\Log',
	];

	/**
	 * Displays a list of all migrations and whether they've been run or not.
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$runner = Services::migrations();
		$group  = $params['g'] ?? CLI::getOption('g');

		if (is_string($group))
		{
			$runner->setGroup($group);
		}

		// Get all namespaces
		$namespaces = Services::autoloader()->getNamespace();

		// Collection of migration status
		$status = [];

		foreach ($namespaces as $namespace => $path)
		{
			if (in_array($namespace, $this->ignoredNamespaces, true))
			{
				continue;
			}

			if (APP_NAMESPACE !== 'App' && $namespace === 'App')
			{
				continue; // @codeCoverageIgnore
			}

			$migrations = $runner->findNamespaceMigrations($namespace);

			if (empty($migrations))
			{
				continue;
			}

			$history = $runner->getHistory();
			ksort($migrations);

			foreach ($migrations as $uid => $migration)
			{
				$migrations[$uid]->name = mb_substr($migration->name, mb_strpos($migration->name, $uid . '_'));

				$date  = '---';
				$group = '---';
				$batch = '---';

				foreach ($history as $row)
				{
					if ($runner->getObjectUid($row) !== $migration->uid)
					{
						continue;
					}

					$date  = date('Y-m-d H:i:s', $row->time);
					$group = $row->group;
					$batch = $row->batch;
				}

				$status[] = [
					$namespace,
					$migration->version,
					$migration->name,
					$group,
					$date,
					$batch,
				];
			}
		}

		if ($status)
		{
			$headers = [
				CLI::color(lang('Migrations.namespace'), 'yellow'),
				CLI::color(lang('Migrations.version'), 'yellow'),
				CLI::color(lang('Migrations.filename'), 'yellow'),
				CLI::color(lang('Migrations.group'), 'yellow'),
				CLI::color(str_replace(': ', '', lang('Migrations.on')), 'yellow'),
				CLI::color(lang('Migrations.batch'), 'yellow'),
			];

			CLI::table($status, $headers);

			return;
		}

		CLI::error(lang('Migrations.noneFound'), 'light_gray', 'red');
		CLI::newLine();
	}
}
