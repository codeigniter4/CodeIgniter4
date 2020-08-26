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
 * ClearDebugbar Command
 */
class ClearDebugbar extends BaseCommand
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
	protected $name = 'debugbar:clear';

	/**
	 * The Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'debugbar:clear';

	/**
	 * The Command's short description.
	 *
	 * @var string
	 */
	protected $description = 'Clears all debugbar JSON files.';

	/**
	 * Actually runs the command.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function run(array $params)
	{
		helper('filesystem');

		if (! delete_files(WRITEPATH . 'debugbar'))
		{
			// @codeCoverageIgnoreStart
			CLI::error('Error deleting the debugbar JSON files.');
			CLI::newLine();
			return;
			// @codeCoverageIgnoreEnd
		}

		CLI::write('Debugbar cleared.', 'green');
		CLI::newLine();
	}
}
