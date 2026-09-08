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

namespace CodeIgniter\Commands\Generators;

use CodeIgniter\CLI\AbstractGeneratorCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Attributes\GeneratorCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Input\Option;
use CodeIgniter\Controller;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\RESTful\ResourcePresenter;

#[Command(name: 'make:controller', description: 'Generates a new controller file.', group: 'Generators')]
#[GeneratorCommand(
    component: 'Controller',
    template: 'controller.tpl.php',
    directory: 'Controllers',
    classNameLang: 'CLI.generator.className.controller',
)]
class ControllerGenerator extends AbstractGeneratorCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption(new Option(
                name: 'bare',
                shortcut: 'b',
                description: 'Extend CodeIgniter\Controller instead of BaseController.',
            ))
            ->addOption(new Option(
                name: 'restful',
                shortcut: 'r',
                description: 'Extend a RESTful resource: "controller" (default when no value is given) or "presenter".',
                acceptsValue: true,
                valueLabel: 'type',
            ));
    }

    protected function interact(array &$arguments, array &$options): void
    {
        $type = $this->getUnboundOption('restful', $options);

        if (! is_string($type) || $type === 'controller' || $type === 'presenter') {
            return;
        }

        $options['restful'] = CLI::prompt(lang('CLI.generator.parentClass'), ['controller', 'presenter'], 'required');
    }

    protected function execute(array $arguments, array $options): int
    {
        $type = $this->getResourceType();

        if (! in_array($type, [null, 'controller', 'presenter'], true)) {
            CLI::error(lang('CLI.generator.invalidParentClass', [$type]));

            return EXIT_ERROR;
        }

        return $this->generateClass();
    }

    protected function getReplacements(string $class): array
    {
        $parent = $this->getParentClass();

        return ['{useStatement}' => $parent, '{extends}' => class_basename($parent)];
    }

    protected function getTemplateData(string $class): array
    {
        return ['type' => $this->getValidatedOption('bare') === true ? null : $this->getResourceType()];
    }

    private function getParentClass(): string
    {
        if ($this->getValidatedOption('bare') === true) {
            return Controller::class;
        }

        return match ($this->getResourceType()) {
            'controller' => ResourceController::class,
            'presenter'  => ResourcePresenter::class,
            default      => trim(APP_NAMESPACE, '\\') . '\\Controllers\\BaseController',
        };
    }

    /**
     * Returns the RESTful resource type, or `null` when `--restful` was not passed.
     */
    private function getResourceType(): ?string
    {
        if (! $this->hasUnboundOption('restful')) {
            return null;
        }

        $type = $this->getValidatedOption('restful');

        return is_string($type) ? $type : 'controller';
    }
}
