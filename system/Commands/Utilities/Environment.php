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
use CodeIgniter\Config\DotEnv;
use Config\Paths;

/**
 * Command to display the current environment,
 * or set a new one in the `.env` file.
 */
final class Environment extends BaseCommand
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
    protected $name = 'env';

    /**
     * The Command's short description
     *
     * @var string
     */
    protected $description = 'Retrieves the current environment, or set a new one.';

    /**
     * The Command's usage
     *
     * @var string
     */
    protected $usage = 'env [<environment>]';

    /**
     * The Command's arguments
     *
     * @var array<string, string>
     */
    protected $arguments = [
        'environment' => '[Optional] The new environment to set. If none is provided, this will print the current environment.',
    ];

    /**
     * The Command's options
     *
     * @var array<string, string>
     */
    protected $options = [];

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

    /**
     * @return int
     */
    public function run(array $params)
    {
        if ($params === []) {
            CLI::write(sprintf('Your environment is currently set as %s.', CLI::color(service('superglobals')->server('CI_ENVIRONMENT', ENVIRONMENT), 'green')));
            CLI::newLine();

            return EXIT_ERROR;
        }

        $env = strtolower(array_shift($params));

        if ($env === 'testing') {
            CLI::error('The "testing" environment is reserved for PHPUnit testing.', 'light_gray', 'red');
            CLI::error('You will not be able to run spark under a "testing" environment.', 'light_gray', 'red');
            CLI::newLine();

            return EXIT_ERROR;
        }

        if (! in_array($env, self::$knownTypes, true)) {
            CLI::error(sprintf('Invalid environment type "%s". Expected one of "%s".', $env, implode('" and "', self::$knownTypes)), 'light_gray', 'red');
            CLI::newLine();

            return EXIT_ERROR;
        }

        if (! $this->writeNewEnvironmentToEnvFile($env)) {
            CLI::error('Error in writing new environment to .env file.', 'light_gray', 'red');
            CLI::newLine();

            return EXIT_ERROR;
        }

        // force DotEnv to reload the new environment
        // however we cannot redefine the ENVIRONMENT constant
        putenv('CI_ENVIRONMENT');
        unset($_ENV['CI_ENVIRONMENT']);
        service('superglobals')->unsetServer('CI_ENVIRONMENT');
        (new DotEnv((new Paths())->envDirectory ?? ROOTPATH))->load();

        CLI::write(sprintf('Environment is successfully changed to "%s".', $env), 'green');
        CLI::write('The ENVIRONMENT constant will be changed in the next script execution.');
        CLI::newLine();

        return EXIT_SUCCESS;
    }

    /**
     * @see https://regex101.com/r/4sSORp/1 for the regex in action
     */
    private function writeNewEnvironmentToEnvFile(string $newEnv): bool
    {
        $baseEnv = ROOTPATH . 'env';
        $envFile = ((new Paths())->envDirectory ?? ROOTPATH) . '.env';

        if (! is_file($envFile)) {
            if (! is_file($baseEnv)) {
                CLI::write('Both default shipped `env` file and custom `.env` are missing.', 'yellow');
                CLI::write('It is impossible to write the new environment type.', 'yellow');
                CLI::newLine();

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
