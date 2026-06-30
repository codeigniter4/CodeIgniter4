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
use CodeIgniter\Config\DotEnv;
use Config\Paths;

/**
 * Command to display the current environment,
 * or set a new one in the `.env` file.
 */
#[Command(name: 'env', description: 'Retrieves the current environment, or set a new one.', group: 'CodeIgniter')]
class Environment extends AbstractCommand
{
    /**
     * Allowed values for environment. `testing` is excluded
     * since spark won't work on it.
     *
     * @var array<int, string>
     */
    private static array $knownTypes = [
        'production',
        'development',
    ];

    protected function configure(): void
    {
        $this->addArgument(new Argument(
            name: 'environment',
            description: 'The new environment to set. If none is provided, the current environment is printed.',
            default: '',
        ));
    }

    protected function execute(array $arguments, array $options): int
    {
        $env = $arguments['environment'];
        assert(is_string($env));

        if ($env === '') {
            CLI::write(sprintf(
                'Your environment is currently set as %s.',
                CLI::color(service('superglobals')->server('CI_ENVIRONMENT', ENVIRONMENT), 'green'),
            ));

            return EXIT_SUCCESS;
        }

        $env = strtolower($env);

        if ($env === 'testing') {
            CLI::error('The "testing" environment is reserved for PHPUnit testing.', 'light_gray', 'red');
            CLI::error('You will not be able to run spark under a "testing" environment.', 'light_gray', 'red');

            return EXIT_ERROR;
        }

        if (! in_array($env, self::$knownTypes, true)) {
            CLI::error(sprintf(
                'Invalid environment type "%s". Expected one of "%s".',
                $env,
                implode('" and "', self::$knownTypes),
            ), 'light_gray', 'red');

            return EXIT_ERROR;
        }

        if (! $this->writeNewEnvironmentToEnvFile($env)) {
            CLI::error('Error in writing new environment to .env file.', 'light_gray', 'red');

            return EXIT_ERROR;
        }

        // Reload DotEnv with the new environment. The ENVIRONMENT constant
        // only takes the new value on the next script execution.
        putenv('CI_ENVIRONMENT');
        unset($_ENV['CI_ENVIRONMENT']);
        service('superglobals')->unsetServer('CI_ENVIRONMENT');
        (new DotEnv((new Paths())->envDirectory ?? ROOTPATH))->load(); // @phpstan-ignore nullCoalesce.property

        CLI::write(sprintf('Environment is successfully changed to "%s".', $env), 'green');
        CLI::write('The ENVIRONMENT constant will be changed in the next script execution.');

        return EXIT_SUCCESS;
    }

    /**
     * @see https://regex101.com/r/4sSORp/1 for the regex in action
     */
    private function writeNewEnvironmentToEnvFile(string $newEnv): bool
    {
        $baseEnv = ROOTPATH . 'env';
        $envFile = ((new Paths())->envDirectory ?? ROOTPATH) . '.env'; // @phpstan-ignore nullCoalesce.property

        if (! is_file($envFile)) {
            if (! is_file($baseEnv)) {
                CLI::write('Both default shipped `env` file and custom `.env` are missing.', 'yellow');
                CLI::write('It is impossible to write the new environment type.', 'yellow');

                return false;
            }

            copy($baseEnv, $envFile);
        }

        $pattern = preg_quote(service('superglobals')->server('CI_ENVIRONMENT', ENVIRONMENT), '/');
        $pattern = sprintf('/^[#\s]*CI_ENVIRONMENT[=\s]+%s$/m', $pattern);

        return file_put_contents(
            $envFile,
            preg_replace($pattern, "\nCI_ENVIRONMENT = {$newEnv}", file_get_contents($envFile), -1, $count),
        ) !== false && $count > 0;
    }
}
