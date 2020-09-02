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

use Config\Services;
use Psr\Log\LoggerInterface;

/**
 * GeneratorCommand can be used as base class
 * for creating commands that generates a file.
 *
 * @package CodeIgniter\CLI
 */
abstract class GeneratorCommand extends BaseCommand
{
	/**
	 * The group the command is lumped under
	 * when listing commands.
	 *
	 * @var string
	 */
	protected $group = 'Generators';

	/**
	 * Default arguments.
	 *
	 * @var array
	 */
	private $defaultArguments = [
		'name' => 'Class name',
	];

	/**
	 * Default option set.
	 *
	 * @var array
	 */
	private $defaultOptions = [
		'-n'     => 'Set root namespace. Defaults to APP_NAMESPACE.',
		'-force' => 'Force overwrite existing files.',
	];

	/**
	 * The params array for easy access by other methods.
	 *
	 * @var array
	 */
	protected $params = [];

	/**
	 * Constructor.
	 *
	 * @param \Psr\Log\LoggerInterface  $logger
	 * @param \CodeIgniter\CLI\Commands $commands
	 */
	public function __construct(LoggerInterface $logger, Commands $commands)
	{
		$this->arguments = array_merge($this->defaultArguments, $this->arguments);
		$this->options   = array_merge($this->options, $this->defaultOptions);

		parent::__construct($logger, $commands);
	}

	/**
	 * Actually execute a command.
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$this->params = $params;

		// First, we'll get the fully qualified class name from the input,
		// pascalizing it if not yet done. Then we will try to get the file
		// path from this.
		helper('inflector');
		$class = $this->qualifyClassName($this->sanitizeClassName($this->getClassName()));
		$path  = $this->buildPath($class);

		// Next, overwriting files unknowingly is a serious annoyance. So we'll check
		// if we are duplicating things. If the 'force' option is not supplied, we bail.
		if (! (array_key_exists('force', $params) || CLI::getOption('force')) && file_exists($path))
		{
			CLI::error(lang('CLI.generateFileExists', [clean_path($path)]), 'light_gray', 'red');
			CLI::newLine();
			return;
		}

		// Next, check if the directory to save the file is existing.
		$dir = dirname($path);
		if (! is_dir($dir))
		{
			mkdir($dir, 0755, true);
		}

		// Lastly, we'll build the class based on the details we have. We'll be getting our
		// file contents from a template and then we'll do the necessary replacements.
		helper('filesystem');
		if (! write_file($path, $this->sortImports($this->buildClassContents($class))))
		{
			CLI::error(lang('CLI.generateFileError') . clean_path($path), 'light_gray', 'red');
			CLI::newLine();
			return;
		}

		CLI::write(lang('CLI.generateFileSuccess') . CLI::color(clean_path($path), 'green'));
		CLI::newLine();
	}

	/**
	 * Gets the class name from input. This can be overridden
	 * if name is really required by providing a prompt.
	 *
	 * @return string
	 */
	protected function getClassName(): string
	{
		$name = $this->params[0] ?? CLI::getSegment(2);

		return $name ?? '';
	}

	/**
	 * Trims input, normalize separators, and ensures
	 * all paths are in Pascal case.
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	protected function sanitizeClassName(string $class): string
	{
		$class = trim($class);
		$class = str_replace('/', '\\', $class);
		$class = implode('\\', array_map('pascalize', explode('\\', $class)));

		return $class;
	}

	/**
	 * Parses the class name and checks if it is already qualified.
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	protected function qualifyClassName(string $class): string
	{
		$class  = ltrim($class, '\\/');
		$rootNS = $this->getRootNamespace();

		if (strncmp($class, $rootNS, strlen($rootNS)) === 0)
		{
			return $class;
		}

		$class = str_replace('/', '\\', $class);

		return $this->qualifyClassName($this->getNamespacedClass($rootNS, $class));
	}

	/**
	 * Gets the root namespace from input.
	 *
	 * @return string
	 */
	protected function getRootNamespace(): string
	{
		$rootNamespace = $this->params['n'] ?? CLI::getOption('n') ?? APP_NAMESPACE;

		return trim(str_replace('/', '\\', $rootNamespace), '\\');
	}

	/**
	 * Gets the qualified class name.
	 *
	 * @param string $rootNamespace
	 * @param string $class
	 *
	 * @return string
	 */
	abstract protected function getNamespacedClass(string $rootNamespace, string $class): string;

	/**
	 * Builds the file path from the class name.
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	protected function buildPath(string $class): string
	{
		$root = $this->getRootNamespace();
		$name = trim(str_replace($root, '', $class), '\\');

		// Check if the namespace is actually defined and we are not just typing gibberish.
		$base = Services::autoloader()->getNamespace($root);
		if (! $base = reset($base))
		{
			throw new \RuntimeException(lang('CLI.namespaceNotDefined', [$root]));
		}
		$base = realpath($base) ?: $base;

		$path     = $base . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
		$filename = $this->modifyBasename(basename($path));

		return implode(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, $path), 0, -1)) . DIRECTORY_SEPARATOR . $filename;
	}

	/**
	 * Provides last chance for child generators to change
	 * the file's basename before saving. This is useful for
	 * migration files where the basename has a date component.
	 *
	 * @param string $filename
	 *
	 * @return string
	 */
	protected function modifyBasename(string $filename): string
	{
		return $filename;
	}

	/**
	 * Builds the contents for class being generated, doing all
	 * the replacements necessary.
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	protected function buildClassContents(string $class): string
	{
		return $this->setReplacements($this->getTemplate(), $class);
	}

	/**
	 * Gets the template for this class.
	 *
	 * @return string
	 */
	abstract protected function getTemplate(): string;

	/**
	 * Retrieves the namespace part from the fully qualified class name.
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	protected function getNamespace(string $class): string
	{
		return trim(implode('\\', array_slice(explode('\\', $class), 0, -1)), '\\');
	}

	/**
	 * Performs the necessary replacements.
	 *
	 * @param string $template
	 * @param string $class
	 *
	 * @return string
	 */
	protected function setReplacements(string $template, string $class): string
	{
		$namespaces = [
			'DummyNamespace',
			'{ namespace }',
			'{namespace}',
		];
		$classes    = [
			'DummyClass',
			'{ class }',
			'{class}',
		];

		$template = str_replace($namespaces, $this->getNamespace($class), $template);
		$class    = str_replace($this->getNamespace($class) . '\\', '', $class);

		return str_replace($classes, $class, $template);
	}

	/**
	 * Alphabetically sorts the imports for a given template.
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	protected function sortImports(string $template): string
	{
		if (preg_match('/(?P<imports>(?:use [^;]+;$\n?)+)/m', $template, $match))
		{
			$imports = explode("\n", trim($match['imports']));
			sort($imports);

			return str_replace(trim($match['imports']), implode("\n", $imports), $template);
		}

		return $template; // @codeCoverageIgnore
	}
}
