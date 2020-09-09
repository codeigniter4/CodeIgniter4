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

namespace CodeIgniter\Commands\Housekeeping;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * ClearLogs command.
 */
class ClearLogs extends BaseCommand
{
	/**
	 * The group the command is lumped under
	 * when listing commands.
	 *
	 * @var string
	 */
	protected $group = 'Housekeeping';

	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'logs:clear';

	/**
	 * The Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Clears all log files.';

	/**
	 * The Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'logs:clear [option';

	/**
	 * The Command's options
	 *
	 * @var array
	 */
	protected $options = [
		'--force' => 'Force delete of all logs files without prompting.',
	];

	/**
	 * Actually execute a command.
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$force = array_key_exists('force', $params) || CLI::getOption('force');

		if (! $force && CLI::prompt('Are you sure you want to delete the logs?', ['n', 'y']) === 'n')
		{
			// @codeCoverageIgnoreStart
			CLI::error('Deleting logs aborted.', 'light_gray', 'red');
			CLI::error('If you want, use the "-force" option to force delete all log files.', 'light_gray', 'red');
			CLI::newLine();
			return;
			// @codeCoverageIgnoreEnd
		}

		helper('filesystem');

		if (! delete_files(WRITEPATH . 'logs', false, true))
		{
			// @codeCoverageIgnoreStart
			CLI::error('Error in deleting the logs files.', 'light_gray', 'red');
			CLI::newLine();
			return;
			// @codeCoverageIgnoreEnd
		}

		CLI::write('Logs cleared.', 'green');
		CLI::newLine();
	}
}
