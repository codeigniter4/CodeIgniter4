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
use Config\Paths;

/**
 * Rotates the encryption key, demoting the current key to `previousKeys`.
 */
#[Command(
    name: 'key:rotate',
    description: 'Rotates the encryption key, demoting the current key to `encryption.previousKeys` in the `.env` file.',
    group: 'Encryption',
)]
class RotateKey extends AbstractCommand
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
                description: 'Skip the key rotation confirmation.',
            ))
            ->addOption(new Option(
                name: 'length',
                description: 'The length of the random string for the new key, in bytes.',
                requiresValue: true,
                default: '32',
            ))
            ->addOption(new Option(
                name: 'prefix',
                description: 'Prefix for the new key (either hex2bin or base64).',
                requiresValue: true,
                default: 'hex2bin',
            ))
            ->addOption(new Option(
                name: 'keep',
                description: 'Maximum number of previous keys to retain. Older keys are dropped. 0 means unlimited.',
                requiresValue: true,
                default: '0',
            ));
    }

    protected function interact(array &$arguments, array &$options): void
    {
        $prefix = $this->getUnboundOption('prefix', $options);

        if (is_string($prefix) && ! in_array($prefix, self::VALID_PREFIXES, true)) {
            $options['prefix'] = CLI::prompt('Please provide a valid prefix to use.', self::VALID_PREFIXES, 'required');
        }

        if ($this->hasUnboundOption('force', $options)) {
            return;
        }

        if (env('encryption.key', '') === '') {
            return;
        }

        if (CLI::prompt('Rotate encryption key? The current key will be moved to `previousKeys`.', ['n', 'y']) === 'y') {
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

        $currentKey = env('encryption.key', '');

        if ($currentKey === '') {
            CLI::error('No existing `encryption.key` to rotate. Run `spark key:generate` first.');

            return EXIT_ERROR;
        }

        if ($options['force'] === false) {
            if ($this->isInteractive()) {
                CLI::error('Key rotation cancelled.');
            } else {
                CLI::error('Key rotation aborted.');
                CLI::error('If you want, use the "--force" option to force the rotation.');
            }

            return EXIT_ERROR;
        }

        $keep = $options['keep'];

        if (! is_numeric($keep) || (int) $keep < 0) {
            CLI::error('The --keep option must be a non-negative integer.');

            return EXIT_ERROR;
        }

        $length = $options['length'];

        if (! is_numeric($length) || (int) $length < 1) {
            CLI::error('The --length option must be a positive integer.');

            return EXIT_ERROR;
        }

        $previousKeys = $this->mergePreviousKeys($currentKey, $this->parsePreviousKeys(), (int) $keep);

        // Write previousKeys first. If the subsequent `key:generate` call fails,
        // the worst case is a stale-but-still-decryptable `.env` (the rotated-out
        // key is preserved on disk).
        if (! $this->writePreviousKeys($previousKeys)) {
            CLI::error('Error in writing `encryption.previousKeys` to `.env` file.');

            return EXIT_ERROR;
        }

        // Clear `encryption.previousKeys` from all env sources so the DotEnv
        // reload triggered by `key:generate` picks up the new value (DotEnv's
        // `setVariable()` skips vars that are already set).
        putenv('encryption.previousKeys');
        unset($_ENV['encryption.previousKeys']);
        service('superglobals')->unsetServer('encryption.previousKeys');

        $exitCode = $this->callSilently('key:generate', options: [
            'force'  => null,
            'prefix' => $prefix,
            'length' => $length,
        ]);

        if ($exitCode !== EXIT_SUCCESS) {
            return $exitCode; // @codeCoverageIgnore
        }

        $count = count($previousKeys);

        CLI::write(sprintf(
            'Encryption key rotated. %d %s retained for decryption fallback.',
            $count,
            $count === 1 ? 'previous key' : 'previous keys',
        ), 'green');
        CLI::write('Re-encrypt existing data with the new key when ready.', 'yellow');

        return EXIT_SUCCESS;
    }

    /**
     * Reads the existing `encryption.previousKeys` from the environment as a
     * comma-separated list, ignoring blank entries.
     *
     * @return list<string>
     */
    private function parsePreviousKeys(): array
    {
        $raw = env('encryption.previousKeys', '');

        if (! is_string($raw) || $raw === '') {
            return [];
        }

        return array_values(array_filter(
            array_map(trim(...), explode(',', $raw)),
            static fn (string $v): bool => $v !== '',
        ));
    }

    /**
     * Prepends the rotated-out key, deduplicates while preserving newest-first order,
     * and optionally caps the list length.
     *
     * @param list<string> $existing
     *
     * @return list<string>
     */
    private function mergePreviousKeys(string $currentKey, array $existing, int $keep): array
    {
        $merged = [$currentKey, ...$existing];
        $seen   = [];
        $result = [];

        foreach ($merged as $key) {
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $result[]   = $key;
        }

        if ($keep > 0) {
            $result = array_slice($result, 0, $keep);
        }

        return $result;
    }

    /**
     * Replaces or inserts the `encryption.previousKeys` line in the `.env` file.
     * `key:generate` is responsible for the file's existence and the
     * `encryption.key` line; this method only touches `encryption.previousKeys`.
     *
     * @param list<string> $previousKeys
     */
    private function writePreviousKeys(array $previousKeys): bool
    {
        $envFile = ((new Paths())->envDirectory ?? ROOTPATH) . '.env'; // @phpstan-ignore nullCoalesce.property

        if (! is_file($envFile)) {
            return false; // @codeCoverageIgnore
        }

        if (! is_writable($envFile)) {
            return false;
        }

        $contents = (string) file_get_contents($envFile);
        $value    = implode(',', $previousKeys);

        // Match an actual setting line, not a substring buried in a comment. The optional
        // `export` prefix mirrors what DotEnv accepts.
        $previousKeysPattern = '/^(\h*(?:export\h+)?encryption\.previousKeys\h*=\h*)[^\r\n]*$/m';

        if (preg_match($previousKeysPattern, $contents) === 1) {
            $contents = (string) preg_replace($previousKeysPattern, '$1' . $value, $contents, 1);

            return file_put_contents($envFile, $contents) !== false;
        }

        // Insert right after the `encryption.key` line so the two stay grouped.
        $injected = (string) preg_replace(
            '/^(\h*(?:export\h+)?encryption\.key\h*=\h*[^\r\n]*)$/m',
            "$1\nencryption.previousKeys = {$value}",
            $contents,
            1,
        );

        if ($injected === $contents) {
            // @codeCoverageIgnoreStart
            // Fallback: append to the end. Shouldn't trigger because `key:generate`
            // writes the `encryption.key` line just before this method runs.
            $injected = $contents . "\nencryption.previousKeys = {$value}";
            // @codeCoverageIgnoreEnd
        }

        return file_put_contents($envFile, $injected) !== false;
    }
}
