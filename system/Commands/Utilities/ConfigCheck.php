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

use CodeIgniter\Cache\FactoriesCache;
use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Input\Argument;
use CodeIgniter\Config\BaseConfig;
use Config\Optimize;
use Kint\Kint;

/**
 * Check the Config values.
 */
#[Command(name: 'config:check', description: 'Check your config values.', group: 'CodeIgniter')]
class ConfigCheck extends AbstractCommand
{
    protected function configure(): void
    {
        $this->addArgument(new Argument(
            name: 'class_name',
            description: 'The config class to check. Short name or FQCN.',
            required: true,
        ));
    }

    protected function execute(array $arguments, array $options): int
    {
        /** @var class-string<BaseConfig> $class */
        $class = $arguments['class_name'];

        $configCacheEnabled = class_exists(Optimize::class) && (new Optimize())->configCacheEnabled;

        if ($configCacheEnabled) {
            (new FactoriesCache())->load('config');
        }

        $config = config($class);

        if ($config === null) {
            CLI::error(sprintf('Config class "%s" not found.', $class));

            return EXIT_ERROR;
        }

        CLI::write($this->getDump($config));

        CLI::newLine();
        $state = CLI::color($configCacheEnabled ? 'enabled' : 'disabled', 'green');
        CLI::write(sprintf('Config caching: %s', $state));

        return EXIT_SUCCESS;
    }

    /**
     * Renders the config object using Kint when available, otherwise var_dump().
     */
    private function getDump(object $config): string
    {
        if (defined('KINT_DIR') && Kint::$enabled_mode !== false) {
            return $this->getKintD($config);
        }

        return CLI::color($this->getVarDump($config), 'cyan');
    }

    /**
     * Gets object dump by Kint d()
     */
    private function getKintD(object $config): string
    {
        ob_start();
        d($config);
        $output = ob_get_clean();

        $lines = array_slice(explode("\n", trim($output)), 3, -3);

        return implode("\n", $lines);
    }

    /**
     * Gets object dump by var_dump()
     */
    private function getVarDump(object $config): string
    {
        ob_start();
        var_dump($config);
        $output = ob_get_clean();

        return preg_replace(
            '!.*system/Commands/Utilities/ConfigCheck.php.*\n!u',
            '',
            $output,
        );
    }
}
