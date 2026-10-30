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

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Input\Option;
use CodeIgniter\Config\DotEnv;
use CodeIgniter\Encryption\Encryption;
use Config\Paths;

/**
 * Generates a new encryption key and writes it in an `.env` file.
 */
#[Command(name: 'key:generate', description: 'Generates a new encryption key and writes it in an `.env` file.', group: 'Encryption')]
class GenerateKey extends AbstractCommand
{
    /**
     * @var list<string>
     */
    private const VALID_PREFIXES = ['hex2bin', 'base64'];

    protected function configure(): void
    {
        $this
            ->addOption(new Option(
                name: 'force',
                shortcut: 'f',
                description: 'Force overwrite existing key in `.env` file.',
            ))
            ->addOption(new Option(
                name: 'length',
                description: 'The length of the random string that should be returned in bytes.',
                requiresValue: true,
                default: '32',
            ))
            ->addOption(new Option(
                name: 'prefix',
                description: 'Prefix to prepend to encoded key (either hex2bin or base64).',
                requiresValue: true,
                default: 'hex2bin',
            ))
            ->addOption(new Option(
                name: 'show',
                description: 'Shows the generated key in the terminal instead of storing in the `.env` file.',
            ));
    }

    protected function interact(array &$arguments, array &$options): void
    {
        $prefix = $this->getUnboundOption('prefix', $options);

        if (is_string($prefix) && ! in_array($prefix, self::VALID_PREFIXES, true)) {
            $options['prefix'] = $this->getPrompt()->select(
                'Please provide a valid prefix to use.',
                array_combine(self::VALID_PREFIXES, self::VALID_PREFIXES),
            );
        }

        if ($this->hasUnboundOption('show', $options)) {
            return;
        }

        if ($this->hasUnboundOption('force', $options)) {
            return;
        }

        if (env('encryption.key', '') === '') {
            return;
        }

        if ($this->getPrompt()->ask('Overwrite existing key?', ['n', 'y']) === 'y') {
            $options['force'] = null; // simulate the presence of the --force option
        }
    }

    protected function execute(array $arguments, array $options): int
    {
        $prefix = $options['prefix'];
        $output = $this->getOutput();

        if (! in_array($prefix, self::VALID_PREFIXES, true)) {
            $output->error(sprintf('Invalid prefix "%s". Use either "hex2bin" or "base64".', $prefix));

            return EXIT_ERROR;
        }

        $encodedKey = $this->generateRandomKey($prefix, (int) $options['length']);

        if ($options['show'] === true) {
            $output->write($encodedKey, 'yellow');

            return EXIT_SUCCESS;
        }

        $currentKey = env('encryption.key', '');

        if ($currentKey !== '' && $options['force'] === false) {
            if ($this->isInteractive()) {
                $output->write('Setting new encryption key cancelled.', 'yellow');

                return EXIT_SUCCESS;
            }

            $output->error('Setting new encryption key aborted: pass --force to overwrite the existing key in non-interactive mode.');

            return EXIT_ERROR;
        }

        $envFile = ((new Paths())->envDirectory ?? ROOTPATH) . '.env'; // @phpstan-ignore nullCoalesce.property
        $baseEnv = ROOTPATH . 'env';

        if (! is_file($envFile) && ! is_file($baseEnv)) {
            $output->write('Both default shipped `env` file and custom `.env` are missing.', 'yellow');
            $output->write(sprintf('Here\'s your new key instead: %s', $output->color($encodedKey, 'yellow')));

            return EXIT_ERROR;
        }

        if (! $this->writeNewEncryptionKeyToFile($encodedKey, $envFile, $baseEnv)) {
            $output->error(sprintf('Failed to write new encryption key to %s.', clean_path($envFile)));

            return EXIT_ERROR;
        }

        // force DotEnv to reload the new env vars
        putenv('encryption.key');
        unset($_ENV['encryption.key']);
        service('superglobals')->unsetServer('encryption.key');
        $dotenv = new DotEnv((new Paths())->envDirectory ?? ROOTPATH); // @phpstan-ignore nullCoalesce.property
        $dotenv->load();

        $output->write(sprintf('New encryption key written to %s.', clean_path($envFile)), 'green');

        return EXIT_SUCCESS;
    }

    /**
     * Generates a key and encodes it.
     */
    private function generateRandomKey(string $prefix, int $length): string
    {
        $key = Encryption::createKey($length);

        if ($prefix === 'hex2bin') {
            return 'hex2bin:' . bin2hex($key);
        }

        return 'base64:' . base64_encode($key);
    }

    /**
     * Writes the new encryption key to .env file. The caller is responsible
     * for ensuring at least one of `$envFile` or `$baseEnv` exists.
     */
    private function writeNewEncryptionKeyToFile(string $newKey, string $envFile, string $baseEnv): bool
    {
        if (! is_file($envFile)) {
            copy($baseEnv, $envFile);
        }

        if (! is_writable($envFile)) {
            return false;
        }

        $oldFileContents = (string) file_get_contents($envFile);

        // Match an active setting line, preserving any leading whitespace and `export` prefix.
        $activePattern = '/^(\h*(?:export\h+)?encryption\.key\h*=\h*)[^\r\n]*$/m';

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
}
