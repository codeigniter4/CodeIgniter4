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

namespace CodeIgniter\Commands\Utilities;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

/**
 * Lists all of the user-defined routes. This will include any Routes files
 * that can be discovered, but will NOT include any routes that are not defined
 * in a routes file, but are instead discovered through auto-routing.
 *
 * @package CodeIgniter\Commands
 */
class Routes extends BaseCommand
{

	/**
	 * The group the command is lumped under
	 * when listing commands.
	 *
	 * @var string
	 */
	protected $group = 'CodeIgniter';

	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'routes';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Displays all of user-defined routes. Does NOT display auto-detected routes.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'routes';

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
	protected $options = [];

	//--------------------------------------------------------------------

	/**
	 * Displays the help for the spark cli script itself.
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$collection = Services::routes(true);
		$methods    = [
			'get',
			'head',
			'post',
			'patch',
			'put',
			'delete',
			'options',
			'trace',
			'connect',
			'cli',
		];

		$tbody = [];
		foreach ($methods as $method)
		{
			$routes = $collection->getRoutes($method);

			foreach ($routes as $from => $to)
			{
				// filter for strings, as callbacks aren't displayable
				if (is_string($to))
				{
					$tbody[] = [
						$from,
						$method,
						$to,
					];
				}
			}
		}

		$thead = [
			'Route',
			'Method',
			'Command',
		];

		CLI::table($tbody, $thead);
	}

}
