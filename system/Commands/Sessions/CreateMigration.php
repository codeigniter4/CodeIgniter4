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

namespace CodeIgniter\Commands\Sessions;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\App;

/**
 * Creates a migration file for database sessions.
 *
 * @package CodeIgniter\Commands
 */

class CreateMigration extends BaseCommand
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
	protected $name = 'session:migration';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Generates the migration file for database sessions.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'session:migration';

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
		'-n' => 'Set migration namespace',
		'-g' => 'Set database group',
		'-t' => 'Set table name',
	];

	/**
	 * Creates a new migration file with the current timestamp.
	 *
	 * @param array $params
	 */
	public function run(array $params = [])
	{
		$config = new App();

		$tableName = CLI::getOption('t') ?? 'ci_sessions';

		$path = APPPATH . 'Database/Migrations/' . date('YmdHis_') . 'create_' . $tableName . '_table' . '.php';

		$data = [
			'namespace' => CLI::getOption('n') ?? APP_NAMESPACE ?? 'App',
			'DBGroup'   => CLI::getOption('g'),
			'tableName' => $tableName,
			'matchIP'   => $config->sessionMatchIP ?? false,
		];

		$template = view('\CodeIgniter\Commands\Sessions\Views\migration.tpl.php', $data, ['debug' => false]);
		$template = str_replace('@php', '<?php', $template);

		// Write the file out.
		helper('filesystem');
		if (! write_file($path, $template))
		{
			CLI::error(lang('Migrations.migWriteError'));
			return;
		}

		CLI::write('Created file: ' . CLI::color(str_replace(APPPATH, 'APPPATH/', $path), 'green'));
	}

}
