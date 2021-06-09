<?php

namespace CodeIgniter\Commands\Utilities;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\DotEnv;

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
     * @var array
     */
    protected $options = [];

    /**
     * Allowed values for environment. `testing` is excluded
     * since spark won't work on it.
     *
     * @var array<int, string>
     */
    private static $knownTypes = [
        'production',
        'development',
    ];

    /**
     * @inheritDoc
     *
     * @param array<string, mixed> $params
     *
     * @return void
     */
    public function run(array $params)
    {
        if ($params === []) {
            CLI::write(sprintf('Your environment is currently set as %s.', CLI::color($_SERVER['CI_ENVIRONMENT'] ?? ENVIRONMENT, 'green')));
            CLI::newLine();

            return;
        }

        $env = strtolower(array_shift($params));

        if ($env === 'testing') {
            CLI::error('The "testing" environment is reserved for PHPUnit testing.', 'light_gray', 'red');
            CLI::error('You will not be able to run spark under a "testing" environment.', 'light_gray', 'red');
            CLI::newLine();

            return;
        }

        if (! in_array($env, self::$knownTypes, true)) {
            CLI::error(sprintf('Invalid environment type "%s". Expected one of "%s".', $env, implode('" and "', self::$knownTypes)), 'light_gray', 'red');
            CLI::newLine();

            return;
        }

        if (! $this->writeNewEnvironmentToEnvFile($env)) {
            CLI::error('Error in writing new environment to .env file.', 'light_gray', 'red');
            CLI::newLine();

            return;
        }

        // force DotEnv to reload the new environment
        // however we cannot redefine the ENVIRONMENT constant
        putenv('CI_ENVIRONMENT');
        unset($_ENV['CI_ENVIRONMENT'], $_SERVER['CI_ENVIRONMENT']);
        (new DotEnv(ROOTPATH))->load();

        CLI::write(sprintf('Environment is successfully changed to "%s".', $env), 'green');
        CLI::write('The ENVIRONMENT constant will be changed in the next script execution.');
        CLI::newLine();
    }

    /**
     * @see https://regex101.com/r/4sSORp/1 for the regex in action
     *
     * @param string $newEnv
     *
     * @return bool
     */
    private function writeNewEnvironmentToEnvFile(string $newEnv): bool
    {
        $baseEnv = ROOTPATH . 'env';
        $envFile = ROOTPATH . '.env';

        if (! is_file($envFile)) {
            if (! is_file($baseEnv)) {
                CLI::write('Both default shipped `env` file and custom `.env` are missing.', 'yellow');
                CLI::write('It is impossible to write the new environment type.', 'yellow');
                CLI::newLine();

                return false;
            }

            copy($baseEnv, $envFile);
        }

        $pattern = preg_quote($_SERVER['CI_ENVIRONMENT'] ?? ENVIRONMENT, '/');
        $pattern = sprintf('/^[#\s]*CI_ENVIRONMENT[=\s]+%s$/m', $pattern);

        return file_put_contents(
            $envFile,
            preg_replace($pattern, "\nCI_ENVIRONMENT = {$newEnv}", file_get_contents($envFile), -1, $count)
        ) !== false && $count > 0;
    }
}
