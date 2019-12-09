<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019 CodeIgniter Foundation
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
 * @copyright  2019 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;
use Config\Autoload;

/**
 * Runs all of the migrations in reverse order, until they have
 * all been un-applied.
 *
 * @package CodeIgniter\Commands
 */
class MigrateRollback extends BaseCommand
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
	protected $name = 'migrate:rollback';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Runs the "down" method for all migrations in the last batch.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'migrate:rollback [Options]';

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
		'-b' => 'Specify a batch to roll back to; e.g. "3" to return to batch #3 or "-2" to roll back twice',
		'-g' => 'Set database group',
	];

	/**
	 * Runs all of the migrations in reverse order, until they have
	 * all been un-applied.
	 *
	 * @param array $params
	 */
	public function run(array $params = [])
	{
		$runner = Services::migrations();

		$group = $params['-g'] ?? CLI::getOption('g');

		if (! is_null($group))
		{
			$runner->setGroup($group);
		}

		try
		{
			$batch = $params['-b'] ?? CLI::getOption('b') ?? $runner->getLastBatch() - 1;
			CLI::write(lang('Migrations.rollingBack') . ' ' . $batch, 'yellow');

			if (! $runner->regress($batch))
			{
				CLI::write(lang('Migrations.generalFault'), 'red');
			}

			$messages = $runner->getCliMessages();
			foreach ($messages as $message)
			{
				CLI::write($message);
			}

			CLI::write('Done');
		}
		catch (\Exception $e)
		{
			$this->showError($e);
		}
	}
}
