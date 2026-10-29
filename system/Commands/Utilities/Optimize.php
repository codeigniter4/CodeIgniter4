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

use CodeIgniter\Autoloader\FileLocator;
use CodeIgniter\Autoloader\FileLocatorCached;
use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Output;
use CodeIgniter\Exceptions\RuntimeException;
use CodeIgniter\Publisher\Publisher;

/**
 * Optimize for production.
 */
#[Command(name: 'optimize', description: 'Optimize for production.', group: 'CodeIgniter')]
final class Optimize extends AbstractCommand
{
    protected function execute(array $arguments, array $options): int
    {
        $output = $this->getOutput();

        try {
            $this->enableCaching($output);
            $this->clearCache($output);
            $this->removeDevPackages($output);

            return EXIT_SUCCESS;
        } catch (RuntimeException) {
            $output->error('The "spark optimize" failed.');

            return EXIT_ERROR;
        }
    }

    private function clearCache(Output $output): void
    {
        $locator = service('locator');

        if (! $locator instanceof FileLocatorCached) {
            $locator = new FileLocatorCached(new FileLocator(service('autoloader')));
        }

        $locator->deleteCache();
        $output->write('Removed FileLocatorCache.', 'green');

        $this->removeFile($output, WRITEPATH . 'cache/FactoriesCache_config');
    }

    private function removeFile(Output $output, string $file): void
    {
        if (! is_file($file)) {
            return;
        }

        if (unlink($file)) {
            $output->write(sprintf('Removed "%s".', clean_path($file)), 'green');

            return;
        }

        $output->error(sprintf('Error in removing file: %s', clean_path($file)));

        throw new RuntimeException(__METHOD__);
    }

    private function enableCaching(Output $output): void
    {
        $publisher = new Publisher(APPPATH, APPPATH);

        $config = APPPATH . 'Config/Optimize.php';

        $result = $publisher->replace(
            $config,
            [
                'public bool $configCacheEnabled = false;'  => 'public bool $configCacheEnabled = true;',
                'public bool $locatorCacheEnabled = false;' => 'public bool $locatorCacheEnabled = true;',
            ],
        );

        if ($result) {
            $output->write(
                'Config Caching and FileLocator Caching are enabled in "app/Config/Optimize.php".',
                'green',
            );

            return;
        }

        $output->error(sprintf('Error in updating file: %s', clean_path($config)));

        throw new RuntimeException(__METHOD__);
    }

    private function removeDevPackages(Output $output): void
    {
        if (! defined('VENDORPATH')) {
            return;
        }

        chdir(ROOTPATH);
        passthru('composer install --no-dev', $status);

        if ($status === 0) {
            $output->write('Removed Composer dev packages.', 'green');

            return;
        }

        $output->error('Error in removing Composer dev packages.');

        throw new RuntimeException(__METHOD__);
    }
}
