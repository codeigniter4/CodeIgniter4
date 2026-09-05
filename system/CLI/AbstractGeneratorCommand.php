<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\CLI;

use CodeIgniter\CLI\Attributes\GeneratorCommand;
use CodeIgniter\CLI\Input\Argument;
use CodeIgniter\CLI\Input\Option;
use CodeIgniter\Exceptions\LogicException;
use Config\Generators;
use Throwable;

/**
 * Base class for modern spark commands that generate files from templates.
 */
abstract class AbstractGeneratorCommand extends AbstractCommand implements PromptsForMissingInputInterface
{
    private const CLASS_NAME_PATTERN = '/^[a-zA-Z_][a-zA-Z0-9_]*(?:\\\\[a-zA-Z_][a-zA-Z0-9_]*)*$/';

    /**
     * @var non-empty-string
     */
    protected readonly string $component;

    /**
     * @var non-empty-string|null
     */
    protected readonly ?string $directory;

    /**
     * @var non-empty-string|null
     */
    protected readonly ?string $namespace;

    protected readonly bool $sortImports;
    protected readonly string $classNameLang;

    /**
     * @var non-empty-string
     */
    protected string $template;

    protected ?string $templatePath = null;

    /**
     * @throws LogicException
     */
    public function __construct(Commands $commands)
    {
        $attribute = $this->resolveClassAttribute(GeneratorCommand::class);

        $this->component     = $attribute->component;
        $this->directory     = $attribute->directory;
        $this->namespace     = $attribute->namespace;
        $this->sortImports   = $attribute->sortImports;
        $this->template      = $attribute->template;
        $this->classNameLang = $attribute->classNameLang;

        parent::__construct($commands);
    }

    protected function configure(): void
    {
        $this->addArgument(new Argument(
            name: 'name',
            description: 'The name of the class to generate.',
            required: true,
        ));
    }

    protected function provideDefaultOptions(): void
    {
        parent::provideDefaultOptions();

        $this->provideGeneratorOptions();
    }

    /**
     * Registers the common generator options.
     */
    protected function provideGeneratorOptions(): void
    {
        $this->addNamespaceOption()->addSuffixOption()->addForceOption();
    }

    final protected function addNamespaceOption(): static
    {
        return $this->addOption(new Option(
            name: 'namespace',
            shortcut: 'n',
            description: 'Set the root namespace.',
            requiresValue: true,
            default: APP_NAMESPACE,
        ));
    }

    final protected function addSuffixOption(): static
    {
        return $this->addOption(new Option(
            name: 'suffix',
            shortcut: 's',
            description: sprintf('Append the "%s" suffix to the class name.', $this->component),
        ));
    }

    final protected function addForceOption(): static
    {
        return $this->addOption(new Option(
            name: 'force',
            shortcut: 'f',
            description: 'Force overwrite existing file.',
        ));
    }

    protected function getArgumentPromptLabels(): array
    {
        return ['name' => lang($this->classNameLang)];
    }

    protected function execute(array $arguments, array $options): int
    {
        return $this->generateClass();
    }

    /**
     * Generates a class file from the configured template.
     */
    protected function generateClass(): int
    {
        return $this->generate($this->qualifyClassName());
    }

    /**
     * Generates a view file from the configured template.
     *
     * @param string $view Namespaced view name that is generated.
     */
    protected function generateView(string $view): int
    {
        return $this->generate($view);
    }

    /**
     * Additional template placeholder replacements, which take precedence over the core `{namespace}` / `{class}` pairs.
     *
     * @param string $class Namespaced classname or namespaced view.
     *
     * @return array<string, string>
     */
    protected function getReplacements(string $class): array
    {
        return [];
    }

    /**
     * View data passed to the generator view when rendering.
     *
     * @param string $class Namespaced classname or namespaced view.
     *
     * @return array<string, mixed>
     */
    protected function getTemplateData(string $class): array
    {
        return [];
    }

    /**
     * Changes the file basename before saving.
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
        $class = $this->normalizeInputClassName();

        $namespace = $this->getNamespace() . '\\';

        if (str_starts_with($class, $namespace)) {
            return $class;
        }

        $directory = ($this->directory !== null) ? $this->directory . '\\' : '';

        return $namespace . $directory . str_replace('/', '\\', $class);
    }

    /**
     * Whether the component suffix should be appended to the class name.
     */
    protected function shouldAppendSuffix(): bool
    {
        return $this->hasOption('suffix') && $this->getValidatedOption('suffix') === true;
    }

    /**
     * Renders the generator view from `$templatePath`, `Config\Generators::$views`, or the `$template` fallback.
     *
     * @param array<string, mixed> $data
     */
    protected function renderTemplate(array $data = []): string
    {
        $fallback = sprintf('CodeIgniter\\Commands\\Generators\\Views\\%s', $this->template);
        $view     = $this->templatePath ?? config(Generators::class)->views[$this->getName()] ?? null;

        if (! is_string($view)) {
            return view($fallback, $data, ['debug' => false]);
        }

        try {
            return view($view, $data, ['debug' => false]);
        } catch (Throwable $e) {
            log_message('error', (string) $e);

            return view($fallback, $data, ['debug' => false]);
        }
    }

    /**
     * Builds the generated file contents, alphabetically sorting the imports when configured.
     */
    protected function buildContent(string $class): string
    {
        $template = $this->parseTemplate($class);

        if (
            $this->sortImports
            && preg_match('/(?P<imports>(?:^use [^;]+;$\n?)+)/m', $template, $match) === 1
        ) {
            $imports = explode("\n", trim($match['imports']));
            sort($imports);

            return str_replace(trim($match['imports']), implode("\n", $imports), $template);
        }

        return $template;
    }

    /**
     * Builds the file path from the class name.
     *
     * @param string $class Namespaced classname or namespaced view.
     */
    protected function buildPath(string $class): string
    {
        $namespace = $this->getNamespace();

        $bases = service('autoloader')->getNamespace($namespace);
        $base  = reset($bases);

        if ($base === false || $base === '') {
            CLI::error(lang('CLI.namespaceNotDefined', [$namespace]));

            return '';
        }

        $realpath = realpath($base);
        $base     = ($realpath !== false) ? $realpath : $base;

        $prefix   = $namespace . '\\';
        $relative = str_starts_with($class, $prefix) ? substr($class, strlen($prefix)) : $class;

        $file = $base . DIRECTORY_SEPARATOR
            . str_replace('\\', DIRECTORY_SEPARATOR, trim($relative, '\\')) . '.php';

        return dirname($file) . DIRECTORY_SEPARATOR . $this->basename($file);
    }

    /**
     * Gets the root namespace from the attribute override or the `--namespace` option.
     */
    protected function getNamespace(): string
    {
        if ($this->namespace !== null) {
            return $this->namespace;
        }

        $namespace = $this->hasOption('namespace') ? $this->getValidatedOption('namespace') : APP_NAMESPACE;
        assert(is_string($namespace));

        return trim(str_replace('/', '\\', $namespace), '\\');
    }

    /**
     * Builds the target path for the given class and writes the generated content to it.
     */
    private function generate(string $class): int
    {
        if (preg_match(self::CLASS_NAME_PATTERN, $class) !== 1) {
            CLI::error(lang('CLI.generator.invalidClassName', [$class]));

            return EXIT_ERROR;
        }

        $target = $this->buildPath($class);

        if ($target === '') {
            return EXIT_ERROR;
        }

        return $this->generateFile($target, $this->buildContent($class));
    }

    /**
     * Writes the generated file to disk with all the safety checks around that.
     */
    private function generateFile(string $target, string $content): int
    {
        if ($this->getNamespace() === 'CodeIgniter') {
            CLI::write(lang('CLI.generator.usingCINamespace'), 'yellow');

            if (
                $this->isInteractive()
                && CLI::prompt(lang('CLI.generator.confirmContinue'), ['y', 'n'], 'required') === 'n'
            ) {
                CLI::write(lang('CLI.generator.cancelOperation'), 'yellow');

                return EXIT_SUCCESS;
            }
        }

        $isFile = is_file($target);
        $force  = $this->hasOption('force') && $this->getValidatedOption('force') === true;

        if (! $force && $isFile) {
            CLI::error(lang('CLI.generator.fileExist', [clean_path($target)]));

            return EXIT_ERROR;
        }

        $dir = dirname($target);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        helper('filesystem');

        if (! write_file($target, $content)) {
            // @codeCoverageIgnoreStart
            CLI::error(lang('CLI.generator.fileError', [clean_path($target)]));

            return EXIT_ERROR;
            // @codeCoverageIgnoreEnd
        }

        if ($isFile) {
            CLI::write(lang('CLI.generator.fileOverwrite', [clean_path($target)]), 'yellow');
        } else {
            CLI::write(lang('CLI.generator.fileCreate', [clean_path($target)]), 'green');
        }

        return EXIT_SUCCESS;
    }

    /**
     * Performs the placeholder replacements on the rendered template.
     *
     * @param string $class Namespaced classname or namespaced view.
     */
    private function parseTemplate(string $class): string
    {
        $segments  = explode('\\', $class);
        $className = array_pop($segments);

        $replacements = $this->getReplacements($class) + [
            '<@php'       => '<?php',
            '{namespace}' => trim(implode('\\', $segments), '\\'),
            '{class}'     => $className,
        ];

        return strtr($this->renderTemplate($this->getTemplateData($class)), $replacements);
    }

    private function normalizeInputClassName(): string
    {
        $class = $this->getValidatedArgument('name');
        assert(is_string($class));

        helper('inflector');

        $component = singular($this->component);

        $pattern = sprintf('/((?:[a-z][a-z0-9_\/\\\\]*)?)(%s)$/i', preg_quote($component, '/'));

        if (preg_match($pattern, $class, $matches) === 1) {
            $class = $matches[1] . ucfirst($component);
        } elseif ($this->shouldAppendSuffix()) {
            $class .= ucfirst($component);
        }

        $segments = array_filter(
            explode('\\', str_replace('/', '\\', trim($class))),
            static fn (string $segment): bool => $segment !== '',
        );

        return implode('\\', array_map(pascalize(...), $segments));
    }
}
