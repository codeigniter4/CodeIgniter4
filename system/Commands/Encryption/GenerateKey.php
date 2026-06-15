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

namespace CodeIgniter\Commands\Encryption;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\DotEnv;
use CodeIgniter\Encryption\Encryption;
use Config\Paths;

/**
 * Generates a new encryption key.
 */
class GenerateKey extends BaseCommand
{
    /**
     * The Command's group.
     *
     * @var string
     */
    protected $group = 'Encryption';

    /**
     * The Command's name.
     *
     * @var string
     */
    protected $name = 'key:generate';

    /**
     * The Command's usage.
     *
     * @var string
     */
    protected $usage = 'key:generate [options]';

    /**
     * The Command's short description.
     *
     * @var string
     */
    protected $description = 'Generates a new encryption key and writes it in an `.env` file.';

    /**
     * The command's options
     *
     * @var array<string, string>
     */
    protected $options = [
        '--force'  => 'Force overwrite existing key in `.env` file.',
        '--length' => 'The length of the random string that should be returned in bytes. Defaults to 32.',
        '--prefix' => 'Prefix to prepend to encoded key (either hex2bin or base64). Defaults to hex2bin.',
        '--show'   => 'Shows the generated key in the terminal instead of storing in the `.env` file.',
    ];

    /**
     * Actually execute the command.
     */
    public function run(array $params)
    {
        $prefix = $params['prefix'] ?? CLI::getOption('prefix');

        if (in_array($prefix, [null, true], true)) {
            $prefix = 'hex2bin';
        } elseif (! in_array($prefix, ['hex2bin', 'base64'], true)) {
            $prefix = CLI::prompt('Please provide a valid prefix to use.', ['hex2bin', 'base64'], 'required'); // @codeCoverageIgnore
        }

        $length = $params['length'] ?? CLI::getOption('length');

        if (in_array($length, [null, true], true)) {
            $length = 32;
        }

        $encodedKey = $this->generateRandomKey($prefix, $length);

        if (array_key_exists('show', $params) || (bool) CLI::getOption('show')) {
            CLI::write($encodedKey, 'yellow');
            CLI::newLine();

            return;
        }

        if (! $this->setNewEncryptionKey($encodedKey, $params)) {
            CLI::write('Error in setting new encryption key to .env file.', 'light_gray', 'red');
            CLI::newLine();

            return;
        }

        // force DotEnv to reload the new env vars
        putenv('encryption.key');
        unset($_ENV['encryption.key']);
        service('superglobals')->unsetServer('encryption.key');
        $dotenv = new DotEnv((new Paths())->envDirectory ?? ROOTPATH); // @phpstan-ignore nullCoalesce.property
        $dotenv->load();

        CLI::write('Application\'s new encryption key was successfully set.', 'green');
        CLI::newLine();
    }

    /**
     * Generates a key and encodes it.
     */
    protected function generateRandomKey(string $prefix, int $length): string
    {
        $key = Encryption::createKey($length);

        if ($prefix === 'hex2bin') {
            return 'hex2bin:' . bin2hex($key);
        }

        return 'base64:' . base64_encode($key);
    }

    /**
     * Sets the new encryption key in your .env file.
     *
     * @param array<int|string, string|null> $params
     */
    protected function setNewEncryptionKey(string $key, array $params): bool
    {
        $currentKey = env('encryption.key', '');

        if ($currentKey !== '' && ! $this->confirmOverwrite($params)) {
            // Not yet testable since it requires keyboard input
            return false; // @codeCoverageIgnore
        }

        return $this->writeNewEncryptionKeyToFile($currentKey, $key);
    }

    /**
     * Checks whether to overwrite existing encryption key.
     *
     * @param array<int|string, string|null> $params
     */
    protected function confirmOverwrite(array $params): bool
    {
        return (array_key_exists('force', $params) || CLI::getOption('force')) || CLI::prompt('Overwrite existing key?', ['n', 'y']) === 'y';
    }

    /**
     * Writes the new encryption key to .env file.
     */
    protected function writeNewEncryptionKeyToFile(string $oldKey, string $newKey): bool
    {
        $baseEnv = ROOTPATH . 'env';
        $envFile = ((new Paths())->envDirectory ?? ROOTPATH) . '.env'; // @phpstan-ignore nullCoalesce.property

        if (! is_file($envFile)) {
            if (! is_file($baseEnv)) {
                CLI::write('Both default shipped `env` file and custom `.env` are missing.', 'yellow');
                CLI::write('Here\'s your new key instead: ' . CLI::color($newKey, 'yellow'));
                CLI::newLine();

                return false;
            }

            copy($baseEnv, $envFile);
        }

        $oldFileContents = (string) file_get_contents($envFile);

        // Match an active setting line, preserving any leading whitespace and `export` prefix.
        $activePattern = $this->keyPattern($oldKey);

        if (preg_match($activePattern, $oldFileContents) === 1) {
            $newFileContents = (string) preg_replace($activePattern, '$1' . $newKey, $oldFileContents, 1);

            return file_put_contents($envFile, $newFileContents) !== false;
        }

        // Match a commented-out setting line (e.g., from the shipped `env` template) and
        // uncomment it. The optional `export` prefix is dropped on uncomment for predictability.
        $commentedPattern = '/^\h*#\h*(?:export\h+)?encryption\.key\h*=\h*[^\r\n]*$/m';

        if (preg_match($commentedPattern, $oldFileContents) === 1) {
            $newFileContents = (string) preg_replace($commentedPattern, "encryption.key = {$newKey}", $oldFileContents, 1);

            return file_put_contents($envFile, $newFileContents) !== false;
        }

        // No setting present (active or commented); append.
        return file_put_contents($envFile, "\nencryption.key = {$newKey}", FILE_APPEND) !== false;
    }

    /**
     * Returns the regex used to locate an active `encryption.key = ...` setting in the `.env`
     * contents. The single capture group spans everything up to (and including) the `=` and any
     * separating whitespace, so a `preg_replace` substitution preserves an optional `export`
     * prefix while rewriting only the value.
     *
     * The `$oldKey` parameter is retained for backward compatibility with subclasses that
     * override this method; it is no longer consulted because the pattern matches any value.
     */
    protected function keyPattern(string $oldKey): string
    {
        return '/^(\h*(?:export\h+)?encryption\.key\h*=\h*)[^\r\n]*$/m';
    }
}
