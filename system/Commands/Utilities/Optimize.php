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

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Publisher\Publisher;

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
        $this->enableCaching();
        $this->clearCache();
        $this->removeDevPackages();

        return EXIT_SUCCESS;
    }

    private function clearCache()
    {
        $cache = WRITEPATH . 'cache/FileLocatorCache';
        if (is_file($cache)) {
            unlink($cache);
            CLI::write('Removed "' . $cache . '".', 'green');
        }

        $cache = WRITEPATH . 'cache/FactoriesCache_config';
        if (is_file($cache)) {
            unlink($cache);
            CLI::write('Removed "' . $cache . '".', 'green');
        }
    }

    private function enableCaching(): void
    {
        $publisher = new Publisher(APPPATH, APPPATH);

        $result = $publisher->replace(
            APPPATH . 'Config/Optimize.php',
            [
                'public bool $configCacheEnabled = false;'  => 'public bool $configCacheEnabled = true;',
                'public bool $locatorCacheEnabled = false;' => 'public bool $locatorCacheEnabled = true;',
            ]
        );

        if ($result) {
            CLI::write(
                'Config Caching and FileLocator Caching are enabled in "app/Config/Optimize.php".',
                'green'
            );
        }
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
        }
    }
}
