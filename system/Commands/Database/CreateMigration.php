<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
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
 * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Autoload;
use Config\Migrations;

/**
 * Creates a new migration file.
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
	protected $group = 'Database';

	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'migrate:create';

	/**
	 * the Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Creates a new migration file.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'migrate:create [migration_name] [Options]';

	/**
	 * the Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'migration_name' => 'The migration file name',
	];

	/**
	 * the Command's Options
	 *
	 * @var array
	 */
	protected $options = [
		'-n' => 'Set migration namespace',
	];

	/**
	 * Creates a new migration file with the current timestamp.
	 *
	 * @param array $params
	 */
	public function run(array $params = [])
	{
		$name = array_shift($params);

		if (empty($name))
		{
			$name = CLI::prompt(lang('Migrations.nameMigration'));
		}

		if (empty($name))
		{
			CLI::error(lang('Migrations.badCreateName'));
			return;
		}
		$ns       = $params['-n'] ?? CLI::getOption('n');
		$homepath = APPPATH;

		if (! empty($ns))
		{
			// Get all namespaces form  PSR4 paths.
			$config     = new Autoload();
			$namespaces = $config->psr4;

			foreach ($namespaces as $namespace => $path)
			{
				if ($namespace === $ns)
				{
					$homepath = realpath($path);
					break;
				}
			}
		}
		else
		{
			$ns = 'App';
		}

		// Migrations Config
		$config = new Migrations();

		if ($config->type !== 'timestamp' && $config->type !== 'sequential')
		{
			CLI::error(lang('Migrations.invalidType', [$config->type]));
			return;
		}

		// migration Type
		if ($config->type === 'timestamp')
		{
			$fileName = date('YmdHis_') . $name;
		}
		else if ($config->type === 'sequential')
		{
			// default with 001
			$sequence = $params[0] ?? '001';
			// number must be three digits
			if (! is_numeric($sequence) || strlen($sequence) !== 3)
			{
				CLI::error(lang('Migrations.migNumberError'));
				return;
			}

			$fileName = $sequence . '_' . $name;
		}

		// full path
		$path = $homepath . '/Database/Migrations/' . $fileName . '.php';

		$template = <<<EOD
<?php namespace $ns\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_{name} extends Migration
{
	public function up()
	{
		//
	}

	//--------------------------------------------------------------------

	public function down()
	{
		//
	}
}

EOD;
		$template = str_replace('{name}', $name, $template);

		helper('filesystem');
		if (! write_file($path, $template))
		{
			CLI::error(lang('Migrations.writeError'));
			return;
		}

		CLI::write('Created file: ' . CLI::color(str_replace($homepath, $ns, $path), 'green'));
	}

}
