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
use CodeIgniter\CLI\Input\Argument;
use CodeIgniter\Security\CheckPhpIni;

/**
 * Check php.ini values.
 */
#[Command(
    name: 'phpini:check',
    description: 'Check your php.ini values in production environment.',
    group: 'CodeIgniter',
)]
final class PhpIniCheck extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument(new Argument(
                name: 'section',
                description: 'The section to check in detail. Only "opcache" is supported.',
                default: '',
            ))
            ->addUsage('phpini:check opcache');
    }

    protected function execute(array $arguments, array $options): int
    {
        $section = $arguments['section'];

        if ($section !== '' && $section !== 'opcache') {
            CLI::error('You must specify a correct argument.');

            return EXIT_ERROR;
        }

        CheckPhpIni::run(argument: $section !== '' ? $section : null);

        return EXIT_SUCCESS;
    }
}
