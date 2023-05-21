<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
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
     * @var bool
     */
    private $hasClassName = true;

    /**
     * Whether to sort class imports.
     *
     * @internal
     *
     * @var bool
     */
    private $sortImports = true;

    /**
     * Whether the `--suffix` option has any effect.
     *
     * @internal
     *
     * @var bool
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
     * @deprecated use generateClass() instead
     */
    protected function execute(array $params): void
    {
        $this->generateClass($params);
    }

    /**
     * Generates a class file from an existing template.
     */
    protected function generateClass(array $params)
    {
        $this->params = $params;

        // Get the fully qualified class name from the input.
        $class = $this->qualifyClassName();

        // Get the file path from class name.
        $target = $this->buildPath($class);

        // Check if path is empty.
        if (empty($target)) {
            return;
        }

        $this->generateFile($target, $this->buildContent($class));
    }

    /**
     * Generate a view file from an existing template.
     */
    protected function generateView(string $view, array $params)
    {
        $this->params = $params;

        $target = $this->buildPath($view);

        // Check if path is empty.
        if (empty($target)) {
            return;
        }

        $this->generateFile($target, $this->buildContent($view));
    }

    /**
     * Handles writing the file to disk, and all of the safety checks around that.
     */
    private function generateFile(string $target, string $content): void
    {
        if ($this->getOption('namespace') === 'CodeIgniter') {
            // @codeCoverageIgnoreStart
            CLI::write(lang('CLI.generator.usingCINamespace'), 'yellow');
            CLI::newLine();

            if (CLI::prompt('Are you sure you want to continue?', ['y', 'n'], 'required') === 'n') {
                CLI::newLine();
                CLI::write(lang('CLI.generator.cancelOperation'), 'yellow');
                CLI::newLine();

                return;
            }

            CLI::newLine();
            // @codeCoverageIgnoreEnd
        }

        $isFile = is_file($target);

        // Overwriting files unknowingly is a serious annoyance, So we'll check if
        // we are duplicating things, If 'force' option is not supplied, we bail.
        if (! $this->getOption('force') && $isFile) {
            CLI::error(lang('CLI.generator.fileExist', [clean_path($target)]), 'light_gray', 'red');
            CLI::newLine();

            return;
        }

        // Check if the directory to save the file is existing.
        $dir = dirname($target);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        helper('filesystem');

        // Build the class based on the details we have, We'll be getting our file
        // contents from the template, and then we'll do the necessary replacements.
        if (! write_file($target, $content)) {
            // @codeCoverageIgnoreStart
            CLI::error(lang('CLI.generator.fileError', [clean_path($target)]), 'light_gray', 'red');
            CLI::newLine();

            return;
            // @codeCoverageIgnoreEnd
        }

        if ($this->getOption('force') && $isFile) {
            CLI::write(lang('CLI.generator.fileOverwrite', [clean_path($target)]), 'yellow');
            CLI::newLine();

            return;
        }

        CLI::write(lang('CLI.generator.fileCreate', [clean_path($target)]), 'green');
        CLI::newLine();
    }

    /**
     * Prepare options and do the necessary replacements.
     */
    protected function prepare(string $class): string
    {
        return $this->parseTemplate($class);
    }

    /**
     * Change file basename before saving.
     *
     * Useful for components where the file name has a date.
     */
    protected function basename(string $filename): string
    {
        return basename($filename);
    }

    /**
     * Parses the class name and checks if it is already qualified.
     */
    protected function qualifyClassName(): string
    {
        // Gets the class name from input.
        $class = $this->params[0] ?? CLI::getSegment(2);

        if ($class === null && $this->hasClassName) {
            // @codeCoverageIgnoreStart
            $nameLang = $this->classNameLang ?: 'CLI.generator.className.default';
            $class    = CLI::prompt(lang($nameLang), null, 'required');
            CLI::newLine();
            // @codeCoverageIgnoreEnd
        }

        helper('inflector');

        $component = singular($this->component);

        /**
         * @see https://regex101.com/r/a5KNCR/2
         */
        $pattern = sprintf('/([a-z][a-z0-9_\/\\\\]+)(%s)$/i', $component);

        if (preg_match($pattern, $class, $matches) === 1) {
            $class = $matches[1] . ucfirst($matches[2]);
        }

        if ($this->enabledSuffixing && $this->getOption('suffix') && preg_match($pattern, $class) !== 1) {
            $class .= ucfirst($component);
        }

        // Trims input, normalize separators, and ensure that all paths are in Pascalcase.
        $class = ltrim(implode('\\', array_map('pascalize', explode('\\', str_replace('/', '\\', trim($class))))), '\\/');

        // Gets the namespace from input. Don't forget the ending backslash!
        $namespace = trim(str_replace('/', '\\', $this->getOption('namespace') ?? APP_NAMESPACE), '\\') . '\\';

        if (strncmp($class, $namespace, strlen($namespace)) === 0) {
            return $class; // @codeCoverageIgnore
        }

        return $namespace . $this->directory . '\\' . str_replace('/', '\\', $class);
    }

    /**
     * Gets the generator view as defined in the `Config\Generators::$views`,
     * with fallback to `$template` when the defined view does not exist.
     */
    protected function renderTemplate(array $data = []): string
    {
        try {
            return view(config('Generators')->views[$this->name], $data, ['debug' => false]);
        } catch (Throwable $e) {
            log_message('error', (string) $e);

            return view("CodeIgniter\\Commands\\Generators\\Views\\{$this->template}", $data, ['debug' => false]);
        }
    }

    /**
     * Performs pseudo-variables contained within view file.
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
     */
    protected function buildContent(string $class): string
    {
        $template = $this->prepare($class);

        if ($this->sortImports && preg_match('/(?P<imports>(?:^use [^;]+;$\n?)+)/m', $template, $match)) {
            $imports = explode("\n", trim($match['imports']));
            sort($imports);

            return str_replace(trim($match['imports']), implode("\n", $imports), $template);
        }

        return $template;
    }

    /**
     * Builds the file path from the class name.
     */
    protected function buildPath(string $class): string
    {
        $namespace = trim(str_replace('/', '\\', $this->getOption('namespace') ?? APP_NAMESPACE), '\\');

        // Check if the namespace is actually defined and we are not just typing gibberish.
        $base = Services::autoloader()->getNamespace($namespace);

        if (! $base = reset($base)) {
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
     * @return mixed
     */
    protected function getOption(string $name)
    {
        if (! array_key_exists($name, $this->params)) {
            return CLI::getOption($name);
        }

        return $this->params[$name] ?? true;
    }
}
