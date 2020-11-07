<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
		'--bare'    => 'Extends from CodeIgniter\\Controller instead of BaseController',
		'--restful' => 'Extends from a RESTful resource. Options are \'controller\' or \'presenter\'.',
	];

	/**
	 * {@inheritDoc}
	 */
	protected function getClassName(): string
	{
		$className = parent::getClassName();

		if (empty($className))
		{
			$className = CLI::prompt(lang('CLI.generateClassName'), null, 'required'); // @codeCoverageIgnore
		}

		return $className;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getNamespacedClass(string $rootNamespace, string $class): string
	{
		return $rootNamespace . '\\Controllers\\' . $class;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getTemplate(): string
	{
		$template = $this->getGeneratorViewFile('CodeIgniter\\Commands\\Generators\\Views\\controller.tpl.php');

		return str_replace('<@php', '<?php', $template);
	}

	/**
	 * {@inheritDoc}
	 */
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

		return str_replace([
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

	/**
	 * If the controller extends any RESTful controller, this will provide
	 * the additional REST API methods.
	 *
	 * @return string
	 */
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
