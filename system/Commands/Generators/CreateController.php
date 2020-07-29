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

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

/**
 * Creates a new controller file.
 *
 * @package CodeIgniter\Commands
 */
class CreateController extends BaseCommand
{
	/**
	 * The group the command is lumped under when listing commands.
	 *
	 * @var String
	 */
	protected $group = 'Generators';

	/**
	 * The Command's name
	 *
	 * @var String
	 */
	protected $name = 'make:controller';

	/**
	 * The Command's short description
	 *
	 * @var String
	 */
	protected $description = 'Creates a new controller file.';

	/**
	 * The Command's usage
	 *
	 * @var String
	 */
	protected $usage = 'make:controller [name] [options]';

	/**
	 * The Command's arguments
	 *
	 * @var Array
	 */
	protected $arguments = [
		'name' => 'The controller file name',
	];

	/**
	 * The Command's options
	 *
	 * @var Array
	 */
	protected $options = [
		'-n' => 'Set controller namespace',
	];

	/**
	 * Creates a new controller file.
	 *
	 * @param Array $params
	 */
	public function run(Array $params): void
	{
		helper('inflector');

		$name = array_shift($params);

		if (empty($name))
		{
			$name = CLI::prompt(lang('Controller.nameFile'), null, 'required');
		}

		$ns       = $params['-n'] ?? CLI::getOption('n');
		$homepath = APPPATH;

		if (! empty($ns))
		{
			// Get all namespaces
			$namespaces = Services::autoloader()->getNamespace();

			foreach ($namespaces as $namespace => $path)
			{
				if ($namespace === $ns)
				{
					$homepath = realpath(reset($path)) . DIRECTORY_SEPARATOR;
					break;
				}
			}
		}
		else
		{
			$ns = defined('APP_NAMESPACE') ? APP_NAMESPACE : 'App';
		}

		// Full path
		$path = $homepath . 'Controllers/' . $name . '.php';

		// Class name should be pascal case now (camel case with upper first letter)
		$name = pascalize($name);

		$template = <<<EOD
		<?php namespace $ns\Controllers;

		class {name} extends BaseController
		{
			public function index()
			{
				echo '{name}Controller';
			}
		}
		
		EOD;

		$template = str_replace('{name}', $name, $template);

		helper('filesystem');

		if (! write_file($path, $template))
		{
			CLI::error(lang('Controller.writeError', [$path]));
			return;
		}

		$ns = rtrim(str_replace('\\', DIRECTORY_SEPARATOR, $ns), '\\') . DIRECTORY_SEPARATOR;
		CLI::write('Created file: ' . CLI::color(str_replace($homepath, $ns, $path), 'green'));
	}
}
