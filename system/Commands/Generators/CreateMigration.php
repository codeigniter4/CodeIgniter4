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

use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\GeneratorCommand;

/**
 * Creates a new migration file.
 *
 * @package CodeIgniter\Commands
 */
class CreateMigration extends GeneratorCommand
{
	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'migrate:create';

	/**
	 * The Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Creates a new migration file.';

	/**
	 * The Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'migrate:create <name> [options]';

	/**
	 * The Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The migration file name',
	];

	/**
	 * Gets the class name from input.
	 *
	 * @return string
	 */
	protected function getClassName(): string
	{
		$class = parent::getClassName();
		if (empty($class))
		{
			// @codeCoverageIgnoreStart
			$class = CLI::prompt(lang('Migrations.nameMigration'), null, 'required');
			// @codeCoverageIgnoreEnd
		}

		return $class;
	}

	/**
	 * Gets the qualified class name.
	 *
	 * @param string $rootNamespace
	 * @param string $class
	 *
	 * @return string
	 */
	protected function getNamespacedClass(string $rootNamespace, string $class): string
	{
		return $rootNamespace . '\\Database\\Migrations\\' . $class;
	}

	protected function modifyBasename(string $filename): string
	{
		return gmdate(config('Migrations')->timestampFormat) . $filename;
	}

	/**
	 * Gets the template for this class.
	 *
	 * @return string
	 */
	protected function getTemplate(): string
	{
		return <<<EOD
<?php

namespace {namespace};

use CodeIgniter\Database\Migration;

class {class} extends Migration
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
	}
}
