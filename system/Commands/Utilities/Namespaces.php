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

namespace CodeIgniter\Commands\Utilities;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Input\Option;
use Config\Autoload;

/**
 * Lists namespaces set in Config\Autoload with their full server path.
 *
 * @see \CodeIgniter\Commands\Utilities\NamespacesTest
 */
#[Command(
    name: 'namespaces',
    description: 'Verifies your namespaces are setup correctly.',
    group: 'CodeIgniter',
)]
class Namespaces extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->addOption(new Option(
                name: 'config-only',
                shortcut: 'c',
                description: 'Show only CodeIgniter config namespaces.',
            ))
            ->addOption(new Option(
                name: 'raw',
                shortcut: 'r',
                description: 'Show raw path strings.',
            ))
            ->addOption(new Option(
                name: 'max-length',
                shortcut: 'm',
                description: 'Specify max length of the path strings to output.',
                requiresValue: true,
                default: '60',
            ));
    }

    protected function execute(array $arguments, array $options): int
    {
        $namespaces = $options['config-only'] !== false
            ? (new Autoload())->psr4
            : service('autoloader')->getNamespace();

        $tbody = $this->buildTable(
            $namespaces,
            $options['raw'] !== false,
            (int) $options['max-length'],
        );

        CLI::table($tbody, ['Namespace', 'Path', 'Found?']);

        return EXIT_SUCCESS;
    }

    /**
     * @param array<string, list<string>|string> $namespaces
     *
     * @return list<list<string>>
     */
    private function buildTable(array $namespaces, bool $raw, int $maxLength): array
    {
        $tbody = [];

        foreach ($namespaces as $namespace => $paths) {
            foreach ((array) $paths as $path) {
                $tbody[] = [
                    $namespace,
                    $this->truncate($raw ? $path : clean_path($path), $maxLength),
                    is_dir($path) ? 'Yes' : 'MISSING',
                ];
            }
        }

        return $tbody;
    }

    private function truncate(string $string, int $max): string
    {
        if (mb_strlen($string) > $max) {
            return mb_substr($string, 0, $max - 3) . '...';
        }

        return $string;
    }
}
