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

class CreateEntity extends GeneratorCommand
{
	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'make:entity';

	/**
	 * The Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Creates a new entity file.';

	/**
	 * The Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'make:entity <name> [options]';

	/**
	 * The Command's arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The model class name',
	];

	protected function getClassName(): string
	{
		$className = parent::getClassName();

		if (empty($className))
		{
			$className = CLI::prompt(lang('CLI.generateClassName'), null, 'required'); // @codeCoverageIgnore
		}

		return $className;
	}

	protected function getNamespacedClass(string $rootNamespace, string $class): string
	{
		return $rootNamespace . '\\Entities\\' . $class;
	}

	protected function getTemplate(): string
	{
		$template = view('CodeIgniter\\Commands\\Generators\\Views\\entity.tpl.php', [], ['debug' => false]);

		return str_replace('<@php', '<?php', $template);
	}
}
