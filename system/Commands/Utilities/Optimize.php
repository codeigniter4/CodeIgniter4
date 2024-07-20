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
use CodeIgniter\Exceptions\RuntimeException;

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
    protected $usage = 'optimize [-c] [-l] [-d]';

    /**
     * The Command's options
     *
     * @var array<string, string>
     */
    protected $options = [
        'c' => 'Enable config caching.',
        'l' => 'Enable locator caching.',
        'd' => 'Disable config and locator caching.',
    ];

    /**
     * {@inheritDoc}
     */
    public function run(array $params)
    {
        // Parse options
        $enableConfigCache  = CLI::getOption('c');
        $enableLocatorCache = CLI::getOption('l');
        $disable            = CLI::getOption('d');

        try {
            $this->enableCaching($enableConfigCache, $enableLocatorCache, $disable);
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

    private function enableCaching(?bool $enableConfigCache, ?bool $enableLocatorCache, ?bool $disable): void
    {
        $publisher = new Publisher(APPPATH, APPPATH);

        $config = APPPATH . 'Config/Optimize.php';

        // Prepare search and replace mappings
        $searchReplace = [];

        if ($disable === true) {
            $searchReplace = [
                'public bool $configCacheEnabled = true;'  => 'public bool $configCacheEnabled = false;',
                'public bool $locatorCacheEnabled = true;' => 'public bool $locatorCacheEnabled = false;',
            ];
        } else {
            if ($enableConfigCache === true) {
                $searchReplace['public bool $configCacheEnabled = false;'] = 'public bool $configCacheEnabled = true;';
            }

            if ($enableLocatorCache === true) {
                $searchReplace['public bool $locatorCacheEnabled = false;'] = 'public bool $locatorCacheEnabled = true;';
            }

            // If no options provided, update both
            if ($enableConfigCache === null && $enableLocatorCache === null) {
                $searchReplace = [
                    'public bool $configCacheEnabled = false;'  => 'public bool $configCacheEnabled = true;',
                    'public bool $locatorCacheEnabled = false;' => 'public bool $locatorCacheEnabled = true;',
                ];
            }
        }

        // Apply replacements if necessary
        if ($searchReplace !== []) {
            $result = $publisher->replace($config, $searchReplace);

            if ($result === true) {
                $messages = [];

                if (in_array('public bool $configCacheEnabled = true;', $searchReplace, true)) {
                    $messages[] = 'Config Caching is enabled in "app/Config/Optimize.php".';
                }

                if (in_array('public bool $locatorCacheEnabled = true;', $searchReplace, true)) {
                    $messages[] = 'FileLocator Caching is enabled in "app/Config/Optimize.php".';
                }

                if (in_array('public bool $configCacheEnabled = false;', $searchReplace, true)) {
                    $messages[] = 'Config Caching is disabled in "app/Config/Optimize.php".';
                }

                if (in_array('public bool $locatorCacheEnabled = false;', $searchReplace, true)) {
                    $messages[] = 'FileLocator Caching is disabled in "app/Config/Optimize.php".';
                }

                CLI::write(implode("\n\n", $messages), 'green');
                CLI::write();

                return;
            }

            CLI::error('Error in updating file: ' . clean_path($config));

            throw new RuntimeException(__METHOD__);
        }

        CLI::write('No changes to caching settings.', 'yellow');
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
