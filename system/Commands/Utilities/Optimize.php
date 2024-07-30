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
use CodeIgniter\Exceptions\RuntimeException;
use CodeIgniter\Publisher\Publisher;

/**
 * Optimize for production.
 */
final class Optimize extends BaseCommand
{
    private const CONFIG_CACHE  = '$configCacheEnabled';
    private const LOCATOR_CACHE = '$locatorCacheEnabled';
    private const CONFIG_PATH   = APPPATH . 'Config/Optimize.php';
    private const CACHE_PATH    = WRITEPATH . 'cache/FactoriesCache_config';

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
        'c' => 'Enable only config caching.',
        'l' => 'Enable only locator caching.',
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
            $this->runCaching($enableConfigCache, $enableLocatorCache, $disable);
            $this->clearCache();
            if ($disable === true) {
                $this->reinstallDevPackages();
            } else {
                $this->removeDevPackages();
            }
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

        $this->removeFile(self::CACHE_PATH);
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

    private function runCaching(?bool $enableConfigCache, ?bool $enableLocatorCache, ?bool $disable): void
    {
        // Prepare search and replace mappings
        $searchReplace = [];

        if ($disable === true) {
            $searchReplace = $this->disableCaching();
        } else {
            $searchReplace = $this->enableCaching(['config' => $enableConfigCache, 'locator' => $enableLocatorCache]);
        }

        // Apply replacements if necessary
        if ($searchReplace !== []) {
            $publisher = new Publisher(APPPATH, APPPATH);

            $result = $publisher->replace(self::CONFIG_PATH, $searchReplace);

            if ($result === true) {
                $messages = [];

                if (in_array('public bool ' . self::CONFIG_CACHE . ' = true;', $searchReplace, true)) {
                    $messages[] = 'Config Caching is enabled in "app/Config/Optimize.php".';
                }

                if (in_array('public bool ' . self::LOCATOR_CACHE . ' = true;', $searchReplace, true)) {
                    $messages[] = 'FileLocator Caching is enabled in "app/Config/Optimize.php".';
                }

                if (in_array('public bool ' . self::CONFIG_CACHE . ' = false;', $searchReplace, true)) {
                    $messages[] = 'Config Caching is disabled in "app/Config/Optimize.php".';
                }

                if (in_array('public bool ' . self::LOCATOR_CACHE . ' = false;', $searchReplace, true)) {
                    $messages[] = 'FileLocator Caching is disabled in "app/Config/Optimize.php".';
                }

                foreach ($messages as $message) {
                    CLI::write($message, 'green');
                    CLI::newLine();
                }

                CLI::newLine();

                return;
            }

            CLI::error('Error in updating file: ' . clean_path(self::CONFIG_PATH));

            throw new RuntimeException(__METHOD__);
        }
        CLI::write('No changes to caching settings.', 'yellow');
    }

    /**
     * Disable Caching
     *
     * @return array<string, string>
     */
    private function disableCaching(): array
    {
        return [
            'public bool ' . self::CONFIG_CACHE . ' = true;'  => 'public bool ' . self::CONFIG_CACHE . ' = false;',
            'public bool ' . self::LOCATOR_CACHE . ' = true;' => 'public bool ' . self::LOCATOR_CACHE . ' = false;',
        ];
    }

    /**
     * Enable Caching
     *
     * @param array<string, bool|null> $options
     *
     * @return array<string, string>
     */
    private function enableCaching(array $options): array
    {
        $searchReplace = [];

        if ($options['config'] === true) {
            $searchReplace['public bool ' . self::CONFIG_CACHE . ' = false;'] = 'public bool ' . self::CONFIG_CACHE . ' = true;';
        }

        if ($options['locator'] === true) {
            $searchReplace['public bool ' . self::LOCATOR_CACHE . ' = false;'] = 'public bool ' . self::LOCATOR_CACHE . ' = true;';
        }

        // If no options provided, update both
        if ($options['config'] === null && $options['locator'] === null) {
            $searchReplace = [
                'public bool ' . self::CONFIG_CACHE . ' = false;'  => 'public bool ' . self::CONFIG_CACHE . ' = true;',
                'public bool ' . self::LOCATOR_CACHE . ' = false;' => 'public bool ' . self::LOCATOR_CACHE . ' = true;',
            ];
        }

        return $searchReplace;
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

    private function reinstallDevPackages(): void
    {
        if (! defined('VENDORPATH')) {
            return;
        }

        chdir(ROOTPATH);
        passthru('composer install', $status);

        if ($status === 0) {
            CLI::write('Installed Composer dev packages.', 'green');

            return;
        }

        CLI::error('Error in installing Composer dev packages.');

        throw new RuntimeException(__METHOD__);
    }
}
