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
use CodeIgniter\CLI\Input\Option;
use CodeIgniter\Publisher\Publisher;

/**
 * Discovers all Publisher classes from the "Publishers/" directory
 * across namespaces. Executes `publish()` from each instance, parsing
 * each result.
 */
#[Command(
    name: 'publish',
    description: 'Discovers and executes all predefined Publisher classes.',
    group: 'CodeIgniter',
)]
class Publish extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument(new Argument(
                name: 'directory',
                description: 'The directory to scan within each namespace.',
                default: 'Publishers',
            ))
            ->addOption(new Option(
                name: 'namespace',
                description: 'The namespace from which to search for files to publish. By default, all namespaces are analysed.',
                requiresValue: true,
                default: '',
            ));
    }

    protected function execute(array $arguments, array $options): int
    {
        $directory = $arguments['directory'];
        $namespace = $options['namespace'];

        $publishers = Publisher::discover($directory, $namespace);

        if ($publishers === []) {
            CLI::write($namespace === ''
                ? lang('Publisher.publishMissing', [$directory])
                : lang('Publisher.publishMissingNamespace', [$directory, $namespace]));

            return EXIT_ERROR;
        }

        $failed = array_reduce(
            $publishers,
            function (bool $carry, Publisher $publisher): bool {
                // Kept out of the return expression so `||` cannot skip publishing after a failure.
                $published = $this->publishOne($publisher);

                return $carry || ! $published;
            },
            false,
        );

        return (int) $failed;
    }

    private function publishOne(Publisher $publisher): bool
    {
        if ($publisher->publish()) {
            CLI::write(lang('Publisher.publishSuccess', [
                $publisher::class,
                count($publisher->getPublished()),
                $publisher->getDestination(),
            ]), 'green');

            return true;
        }

        CLI::error(lang('Publisher.publishFailure', [
            $publisher::class,
            $publisher->getDestination(),
        ]), 'light_gray', 'red');

        foreach ($publisher->getErrors() as $file => $exception) {
            CLI::write($file);
            CLI::error($exception->getMessage());
            CLI::newLine();
        }

        return false;
    }
}
