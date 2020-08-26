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

namespace CodeIgniter\CLI;

use CodeIgniter\Controller;
use Config\Services;

/**
 * Command runner
 */
class CommandRunner extends Controller
{

	/**
	 * The Command Manager
	 *
	 * @var Commands
	 */
	protected $commands;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->commands = Services::commands();
	}

	/**
	 * We map all un-routed CLI methods through this function
	 * so we have the chance to look for a Command first.
	 *
	 * @param string $method
	 * @param array  ...$params
	 *
	 * @return mixed
	 * @throws \ReflectionException
	 */
	public function _remap($method, ...$params)
	{
		// The first param is usually empty, so scrap it.
		if (empty($params[0]))
		{
			array_shift($params);
		}

		return $this->index($params);
	}

	//--------------------------------------------------------------------

	/**
	 * Default command.
	 *
	 * @param array $params
	 *
	 * @return mixed
	 * @throws \ReflectionException
	 */
	public function index(array $params)
	{
		$command = array_shift($params);

		if (is_null($command))
		{
			$command = 'list';
		}

		return service('commands')->run($command, $params);
	}

	/**
	 * Allows access to the current commands that have been found.
	 *
	 * @return array
	 */
	public function getCommands(): array
	{
		return $this->commands->getCommands();
	}
}
