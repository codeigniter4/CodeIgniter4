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
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Publisher\Publisher;
use RuntimeException;

/**
 * Optimize for production.
 */
final class Optimize extends BaseCommand
{
    /**
     * The group the command is lumped under
     * when listing commands.
     *
     * @var string
     */
    protected $group = 'CodeIgniter';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'optimize';

    /**
     * The Command's short description
     *
     * @var string
     */
    protected $description = 'Optimize for production.';

    /**
     * The Command's usage
     *
     * @var string
     */
    protected $usage = 'optimize';

    /**
     * {@inheritDoc}
     */
    public function run(array $params)
    {
        try {
            $this->enableCaching();
            $this->clearCache();
            $this->removeDevPackages();
        } catch (RuntimeException) {
            CLI::error('The "spark optimize" failed.');

            return EXIT_ERROR;
        }

        return EXIT_SUCCESS;
    }

    private function clearCache(): void
    {
        $locator = new FileLocatorCached(new FileLocator(service('autoloader')));
        $locator->deleteCache();
        CLI::write('Removed FileLocatorCache.', 'green');

        $cache = WRITEPATH . 'cache/FactoriesCache_config';
        $this->removeFile($cache);
    }

    private function removeFile(string $cache): void
    {
        if (is_file($cache)) {
            $result = unlink($cache);

            if ($result) {
                CLI::write('Removed "' . clean_path($cache) . '".', 'green');

                return;
            }

            CLI::error('Error in removing file: ' . clean_path($cache));

            throw new RuntimeException(__METHOD__);
        }
    }

    private function enableCaching(): void
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
            CLI::write(
                'Config Caching and FileLocator Caching are enabled in "app/Config/Optimize.php".',
                'green',
            );

            return;
        }

        CLI::error('Error in updating file: ' . clean_path($config));

        throw new RuntimeException(__METHOD__);
    }

    private function removeDevPackages(): void
    {
        if (! defined('VENDORPATH')) {
            return;
        }

        chdir(ROOTPATH);
        passthru('composer install --no-dev', $status);

        if ($status === 0) {
            CLI::write('Removed Composer dev packages.', 'green');

            return;
        }

        CLI::error('Error in removing Composer dev packages.');

        throw new RuntimeException(__METHOD__);
    }
}
