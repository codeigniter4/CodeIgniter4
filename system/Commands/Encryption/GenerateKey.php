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
use CodeIgniter\CLI\CLI;
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
            $options['prefix'] = CLI::prompt('Please provide a valid prefix to use.', self::VALID_PREFIXES, 'required');
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

        if (CLI::prompt('Overwrite existing key?', ['n', 'y']) === 'y') {
            $options['force'] = null; // simulate the presence of the --force option
        }
    }

    protected function execute(array $arguments, array $options): int
    {
        $prefix = $options['prefix'];

        if (! in_array($prefix, self::VALID_PREFIXES, true)) {
            CLI::error(sprintf('Invalid prefix "%s". Use either "hex2bin" or "base64".', $prefix));

            return EXIT_ERROR;
        }

        $encodedKey = $this->generateRandomKey($prefix, (int) $options['length']);

        if ($options['show'] === true) {
            CLI::write($encodedKey, 'yellow');

            return EXIT_SUCCESS;
        }

        $currentKey = env('encryption.key', '');

        if ($currentKey !== '' && $options['force'] === false) {
            CLI::error('Setting new encryption key aborted.');

            if (! $this->isInteractive()) {
                CLI::error('If you want, use the "--force" option to force overwrite the existing key.');
            }

            return EXIT_ERROR;
        }

        if (! $this->writeNewEncryptionKeyToFile($currentKey, $encodedKey)) {
            CLI::write('Error in setting new encryption key to .env file.');

            return EXIT_ERROR;
        }

        // force DotEnv to reload the new env vars
        putenv('encryption.key');
        unset($_ENV['encryption.key'], $_SERVER['encryption.key']);
        $dotenv = new DotEnv((new Paths())->envDirectory ?? ROOTPATH); // @phpstan-ignore nullCoalesce.property
        $dotenv->load();

        CLI::write('Application\'s new encryption key was successfully set.', 'green');
        CLI::newLine();

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
     * Writes the new encryption key to .env file.
     */
    private function writeNewEncryptionKeyToFile(string $oldKey, string $newKey): bool
    {
        $baseEnv = ROOTPATH . 'env';
        $envFile = ((new Paths())->envDirectory ?? ROOTPATH) . '.env'; // @phpstan-ignore nullCoalesce.property

        if (! is_file($envFile)) {
            if (! is_file($baseEnv)) {
                CLI::write('Both default shipped `env` file and custom `.env` are missing.', 'yellow');
                CLI::write('Here\'s your new key instead: ' . CLI::color($newKey, 'yellow'));

                return false;
            }

            copy($baseEnv, $envFile);
        }

        $oldFileContents = (string) file_get_contents($envFile);
        $replacementKey  = "\nencryption.key = {$newKey}";

        if (! str_contains($oldFileContents, 'encryption.key')) {
            return file_put_contents($envFile, $replacementKey, FILE_APPEND) !== false;
        }

        $newFileContents = preg_replace($this->keyPattern($oldKey), $replacementKey, $oldFileContents);

        if ($newFileContents === $oldFileContents) {
            $newFileContents = preg_replace(
                '/^[#\s]*encryption.key[=\s]*(?:hex2bin\:[a-f0-9]{64}|base64\:(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=)?)$/m',
                $replacementKey,
                $oldFileContents,
            );
        }

        return file_put_contents($envFile, $newFileContents) !== false;
    }

    /**
     * Get the regex of the current encryption key.
     */
    private function keyPattern(string $oldKey): string
    {
        $escaped = preg_quote($oldKey, '/');

        if ($escaped !== '') {
            $escaped = "[{$escaped}]*";
        }

        return "/^[#\\s]*encryption.key[=\\s]*{$escaped}$/m";
    }
}
