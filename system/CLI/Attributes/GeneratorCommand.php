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

namespace CodeIgniter\CLI\Attributes;

use Attribute;
use CodeIgniter\Exceptions\LogicException;

/**
 * Attribute holding the code generation configuration of a generator command.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class GeneratorCommand
{
    private const NAMESPACE_PATTERN = '/^[a-zA-Z_][a-zA-Z0-9_]*(?:\\\\[a-zA-Z_][a-zA-Z0-9_]*)*$/';

    /**
     * @var non-empty-string
     */
    public string $component;

    /**
     * @var non-empty-string
     */
    public string $template;

    /**
     * @var non-empty-string|null
     */
    public ?string $directory;

    /**
     * @var non-empty-string|null
     */
    public ?string $namespace;

    /**
     * @param string      $component     The component name appended as suffix to generated class names.
     * @param string      $template      Basename of the fallback view under `CodeIgniter\Commands\Generators\Views`.
     * @param string|null $directory     Sub-namespace under the root namespace where classes are generated.
     * @param string      $classNameLang Lang key for the class name prompt.
     * @param string|null $namespace     Root namespace override, ignoring the `--namespace` option.
     *
     * @throws LogicException
     */
    public function __construct(
        string $component,
        string $template,
        ?string $directory = null,
        public string $classNameLang = 'CLI.generator.className.default',
        ?string $namespace = null,
        public bool $sortImports = true,
    ) {
        if ($component === '') {
            throw new LogicException(lang('Commands.generatorEmptyComponent'));
        }

        if (preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $component) !== 1) {
            throw new LogicException(lang('Commands.generatorInvalidComponent', [$component]));
        }

        if ($template === '') {
            throw new LogicException(lang('Commands.generatorEmptyTemplate'));
        }

        if ($directory === '') {
            throw new LogicException(lang('Commands.generatorEmptyDirectory'));
        }

        if ($directory !== null && preg_match(self::NAMESPACE_PATTERN, $directory) !== 1) {
            throw new LogicException(lang('Commands.generatorInvalidDirectory', [$directory]));
        }

        if ($namespace === '') {
            throw new LogicException(lang('Commands.generatorEmptyNamespace'));
        }

        if ($namespace !== null && preg_match(self::NAMESPACE_PATTERN, $namespace) !== 1) {
            throw new LogicException(lang('Commands.generatorInvalidNamespace', [$namespace]));
        }

        $this->component = $component;
        $this->template  = $template;
        $this->directory = $directory;
        $this->namespace = $namespace;
    }
}
