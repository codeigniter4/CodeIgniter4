<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\CLI;

use Config\Services;
use Throwable;

/**
 * GeneratorTrait contains a collection of methods
 * to build the commands that generates a file.
 */
trait GeneratorTrait
{
	/**
	 * Component Name
	 *
	 * @var string
	 */
	protected $component;

	/**
	 * File directory
	 *
	 * @var string
	 */
	protected $directory;

	/**
	 * View template name
	 *
	 * @var string
	 */
	protected $template;

	/**
	 * Language string key for required class names.
	 *
	 * @var string
	 */
	protected $classNameLang = '';

	/**
	 * Whether to require class name.
	 *
	 * @internal
	 *
	 * @var boolean
	 */
	private $hasClassName = true;

	/**
	 * Whether to sort class imports.
	 *
	 * @internal
	 *
	 * @var boolean
	 */
	private $sortImports = true;

	/**
	 * Whether the `--suffix` option has any effect.
	 *
	 * @internal
	 *
	 * @var boolean
	 */
	private $enabledSuffixing = true;

	/**
	 * The params array for easy access by other methods.
	 *
	 * @internal
	 *
	 * @var array
	 */
	private $params = [];

	/**
	 * Execute the command.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	protected function execute(array $params): void
	{
		$this->params = $params;

		if ($this->getOption('namespace') === 'CodeIgniter')
		{
			// @codeCoverageIgnoreStart
			CLI::write(lang('CLI.generator.usingCINamespace'), 'yellow');
			CLI::newLine();

			if (CLI::prompt('Are you sure you want to continue?', ['y', 'n'], 'required') === 'n')
			{
				CLI::newLine();
				CLI::write(lang('CLI.generator.cancelOperation'), 'yellow');
				CLI::newLine();

				return;
			}

			CLI::newLine();
			// @codeCoverageIgnoreEnd
		}

		// Get the fully qualified class name from the input.
		$class = $this->qualifyClassName();

		// Get the file path from class name.
		$path = $this->buildPath($class);

		// Check if path is empty.
		if (empty($path))
		{
			return;
		}

		$isFile = is_file($path);

		// Overwriting files unknowingly is a serious annoyance, So we'll check if
		// we are duplicating things, If 'force' option is not supplied, we bail.
		if (! $this->getOption('force') && $isFile)
		{
			CLI::error(lang('CLI.generator.fileExist', [clean_path($path)]), 'light_gray', 'red');
			CLI::newLine();

			return;
		}

		// Check if the directory to save the file is existing.
		$dir = dirname($path);

		if (! is_dir($dir))
		{
			mkdir($dir, 0755, true);
		}

		helper('filesystem');

		// Build the class based on the details we have, We'll be getting our file
		// contents from the template, and then we'll do the necessary replacements.
		if (! write_file($path, $this->buildContent($class)))
		{
			// @codeCoverageIgnoreStart
			CLI::error(lang('CLI.generator.fileError', [clean_path($path)]), 'light_gray', 'red');
			CLI::newLine();

			return;
			// @codeCoverageIgnoreEnd
		}

		if ($this->getOption('force') && $isFile)
		{
			CLI::write(lang('CLI.generator.fileOverwrite', [clean_path($path)]), 'yellow');
			CLI::newLine();

			return;
		}

		CLI::write(lang('CLI.generator.fileCreate', [clean_path($path)]), 'green');
		CLI::newLine();
	}

	/**
	 * Prepare options and do the necessary replacements.
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	protected function prepare(string $class): string
	{
		return $this->parseTemplate($class);
	}

	/**
	 * Change file basename before saving.
	 *
	 * Useful for components where the file name has a date.
	 *
	 * @param string $filename
	 *
	 * @return string
	 */
	protected function basename(string $filename): string
	{
		return basename($filename);
	}

	/**
	 * Parses the class name and checks if it is already qualified.
	 *
	 * @return string
	 */
	protected function qualifyClassName(): string
	{
		// Gets the class name from input.
		$class = $this->params[0] ?? CLI::getSegment(2);

		if (is_null($class) && $this->hasClassName)
		{
			// @codeCoverageIgnoreStart
			$nameLang = $this->classNameLang ?: 'CLI.generator.className.default';
			$class    = CLI::prompt(lang($nameLang), null, 'required');
			CLI::newLine();
			// @codeCoverageIgnoreEnd
		}

		helper('inflector');

		$component = singular($this->component);

		/**
		 * @see https://regex101.com/r/a5KNCR/1
		 */
		$pattern = sprintf('/([a-z][a-z0-9_\/\\\\]+)(%s)/i', $component);

		if (preg_match($pattern, $class, $matches) === 1)
		{
			$class = $matches[1] . ucfirst($matches[2]);
		}

		if ($this->enabledSuffixing && $this->getOption('suffix') && ! strripos($class, $component))
		{
			$class .= ucfirst($component);
		}

		// Trims input, normalize separators, and ensure that all paths are in Pascalcase.
		$class = ltrim(implode('\\', array_map('pascalize', explode('\\', str_replace('/', '\\', trim($class))))), '\\/');

		// Gets the namespace from input.
		$namespace = trim(str_replace('/', '\\', $this->getOption('namespace') ?? APP_NAMESPACE), '\\');

		if (strncmp($class, $namespace, strlen($namespace)) === 0)
		{
			return $class; // @codeCoverageIgnore
		}

		return $namespace . '\\' . $this->directory . '\\' . str_replace('/', '\\', $class);
	}

	/**
	 * Gets the generator view as defined in the `Config\Generators::$views`,
	 * with fallback to `$template` when the defined view does not exist.
	 *
	 * @param array $data Data to be passed to the view.
	 *
	 * @return string
	 */
	protected function renderTemplate(array $data = []): string
	{
		try
		{
			return view(config('Generators')->views[$this->name], $data, ['debug' => false]);
		}
		catch (Throwable $e)
		{
			log_message('error', $e->getMessage());

			return view("CodeIgniter\Commands\Generators\Views\\{$this->template}", $data, ['debug' => false]);
		}
	}

	/**
	 * Performs pseudo-variables contained within view file.
	 *
	 * @param string $class
	 * @param array  $search
	 * @param array  $replace
	 * @param array  $data
	 *
	 * @return string
	 */
	protected function parseTemplate(string $class, array $search = [], array $replace = [], array $data = []): string
	{
		// Retrieves the namespace part from the fully qualified class name.
		$namespace = trim(implode('\\', array_slice(explode('\\', $class), 0, -1)), '\\');
		$search[]  = '<@php';
		$search[]  = '{namespace}';
		$search[]  = '{class}';
		$replace[] = '<?php';
		$replace[] = $namespace;
		$replace[] = str_replace($namespace . '\\', '', $class);

		return str_replace($search, $replace, $this->renderTemplate($data));
	}

	/**
	 * Builds the contents for class being generated, doing all
	 * the replacements necessary, and alphabetically sorts the
	 * imports for a given template.
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	protected function buildContent(string $class): string
	{
		$template = $this->prepare($class);

		if ($this->sortImports && preg_match('/(?P<imports>(?:^use [^;]+;$\n?)+)/m', $template, $match))
		{
			$imports = explode("\n", trim($match['imports']));
			sort($imports);

			return str_replace(trim($match['imports']), implode("\n", $imports), $template);
		}

		return $template;
	}

	/**
	 * Builds the file path from the class name.
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	protected function buildPath(string $class): string
	{
		$namespace = trim(str_replace('/', '\\', $this->getOption('namespace') ?? APP_NAMESPACE), '\\');

		// Check if the namespace is actually defined and we are not just typing gibberish.
		$base = Services::autoloader()->getNamespace($namespace);

		if (! $base = reset($base))
		{
			CLI::error(lang('CLI.namespaceNotDefined', [$namespace]), 'light_gray', 'red');
			CLI::newLine();

			return '';
		}

		$base = realpath($base) ?: $base;
		$file = $base . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, trim(str_replace($namespace . '\\', '', $class), '\\')) . '.php';

		return implode(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, $file), 0, -1)) . DIRECTORY_SEPARATOR . $this->basename($file);
	}

	/**
	 * Allows child generators to modify the internal `$hasClassName` flag.
	 *
	 * @param boolean $hasClassName
	 *
	 * @return $this
	 */
	protected function setHasClassName(bool $hasClassName)
	{
		$this->hasClassName = $hasClassName;

		return $this;
	}

	/**
	 * Allows child generators to modify the internal `$sortImports` flag.
	 *
	 * @param boolean $sortImports
	 *
	 * @return $this
	 */
	protected function setSortImports(bool $sortImports)
	{
		$this->sortImports = $sortImports;

		return $this;
	}

	/**
	 * Allows child generators to modify the internal `$enabledSuffixing` flag.
	 *
	 * @param boolean $enabledSuffixing
	 *
	 * @return $this
	 */
	protected function setEnabledSuffixing(bool $enabledSuffixing)
	{
		$this->enabledSuffixing = $enabledSuffixing;

		return $this;
	}

	/**
	 * Gets a single command-line option. Returns TRUE if the option exists,
	 * but doesn't have a value, and is simply acting as a flag.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	protected function getOption(string $name)
	{
		if (! array_key_exists($name, $this->params))
		{
			return CLI::getOption($name);
		}

		return is_null($this->params[$name]) ? true : $this->params[$name];
	}
}
