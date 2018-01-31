<?php namespace CodeIgniter\Commands\Database;

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

/**
 * Does a rollback followed by a latest to refresh the current state
 * of the database.
 *
 * @package CodeIgniter\Commands
 */
class MigrateRefresh extends BaseCommand
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
	protected $name = 'migrate:refresh';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Does a rollback followed by a latest to refresh the current state of the database.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'migrate:refresh [Options]';

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
		'-n'	 => 'Set migration namespace',
		'-g'	 => 'Set database group',
		'-all'	 => 'Set latest for all namespace, will ignore (-n) option'
	];

	/**
	 * Does a rollback followed by a latest to refresh the current state
	 * of the database.
	 *
	 * @param array $params
	 */
	public function run(array $params = [])
	{
		$this->call('migrate:rollback');
		$this->call('migrate:latest');
	}

}
