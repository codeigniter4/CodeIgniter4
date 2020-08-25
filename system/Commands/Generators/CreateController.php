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
 * Creates a skeleton controller file.
 */
class CreateController extends GeneratorCommand
{
	/**
	 * The Command's name
	 *
	 * @var string
	 */
	protected $name = 'make:controller';

	/**
	 * The Command's short description
	 *
	 * @var string
	 */
	protected $description = 'Creates a new controller file.';

	/**
	 * The Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'make:controller <name> [options]';

	/**
	 * The Command's arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'name' => 'The controller class name',
	];

	/**
	 * The Command's options
	 *
	 * @var array
	 */
	protected $options = [
		'-bare'    => 'Extends from CodeIgniter\\Controller instead of BaseController',
		'-restful' => 'Extends from a RESTful resource. Options are \'controller\' or \'presenter\'.',
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
		return $rootNamespace . '\\Controllers\\' . $class;
	}

	protected function getTemplate(): string
	{
		$template = view('CodeIgniter\\Commands\\Generators\\Views\\controller.tpl.php', [], ['debug' => false]);
		$template = str_replace('<@php', '<?php', $template);

		return $template;
	}

	protected function setReplacements(string $template, string $class): string
	{
		$bare = array_key_exists('bare', $this->params) || CLI::getOption('bare');
		$rest = array_key_exists('restful', $this->params)
			? $this->params['restful'] ?? true
			: CLI::getOption('restful');

		[
			$useStatement,
			$extends,
			$restfulMethods,
		] = $this->getParentClass($bare, $rest);

		$template = parent::setReplacements($template, $class);
		$template = str_replace([
			'{useStatement}',
			'{extends}',
			'{restfulMethods}',
		], [
			$useStatement,
			$extends,
			$restfulMethods,
		],
			$template
		);

		return $template;
	}

	/**
	 * Gets the appropriate parent class to extend.
	 *
	 * @param boolean|null        $bare
	 * @param string|boolean|null $rest
	 *
	 * @return array
	 */
	protected function getParentClass(?bool $bare, $rest): array
	{
		$restfulMethods = '';

		if (! $bare && ! $rest)
		{
			$appNamespace = trim(APP_NAMESPACE, '\\');
			$useStatement = "use {$appNamespace}\\Controllers\\BaseController;";
			$extends      = 'extends BaseController';
		}
		elseif ($bare)
		{
			$useStatement = 'use CodeIgniter\\Controller;';
			$extends      = 'extends Controller';
		}
		else
		{
			if ($rest === true)
			{
				$type = 'controller';
			}
			elseif (in_array($rest, ['controller', 'presenter'], true))
			{
				$type = $rest;
			}
			else
			{
				$type = CLI::prompt(lang('CLI.generateParentClass'), ['controller', 'presenter'], 'required'); // @codeCoverageIgnore
			}

			$restfulMethods = $this->getAdditionalRestfulMethods();

			$type         = ucfirst($type);
			$useStatement = "use CodeIgniter\\RESTful\\Resource{$type};";
			$extends      = "extends Resource{$type}";
		}

		return [
			$useStatement,
			$extends,
			$restfulMethods,
		];
	}

	protected function getAdditionalRestfulMethods(): string
	{
		return <<<'EOF'

	/**
	 * Return the properties of a resource object
	 *
	 * @return array
	 */
	public function show($id = null)
	{
		//
	}

	/**
	 * Return a new resource object, with default properties
	 *
	 * @return array
	 */
	public function new()
	{
		//
	}

	/**
	 * Create a new resource object, from "posted" parameters
	 *
	 * @return array
	 */
	public function create()
	{
		//
	}

	/**
	 * Return the editable properties of a resource object
	 *
	 * @return array
	 */
	public function edit($id = null)
	{
		//
	}

	/**
	 * Add or update a model resource, from "posted" properties
	 *
	 * @return array
	 */
	public function update($id = null)
	{
		//
	}

	/**
	 * Delete the designated resource object from the model
	 *
	 * @return array
	 */
	public function delete($id = null)
	{
		//
	}

EOF;
	}
}
