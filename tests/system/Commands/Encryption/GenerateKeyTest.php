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

use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\Services;
use CodeIgniter\Superglobals;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use CodeIgniter\Test\Mock\MockInputOutput;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;

/**
 * @internal
 */
#[Group('SeparateProcess')]
final class GenerateKeyTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private string $envPath;
    private string $backupEnvPath;

    #[WithoutErrorHandler]
    protected function setUp(): void
    {
        parent::setUp();

        CLI::resetLastWrite();
        Services::injectMock('superglobals', new Superglobals());

        $this->envPath       = ROOTPATH . '.env';
        $this->backupEnvPath = ROOTPATH . '.env.backup';

        if (is_file($this->envPath)) {
            rename($this->envPath, $this->backupEnvPath);
        }

        $this->resetEnvironment();
    }

    protected function tearDown(): void
    {
        if (is_file($this->envPath)) {
            unlink($this->envPath);
        }

        if (is_file($this->backupEnvPath)) {
            rename($this->backupEnvPath, $this->envPath);
        }

        $this->resetEnvironment();

        CLI::resetLastWrite();
        CLI::reset();
    }

    /**
     * Gets buffer contents then releases it.
     */
    protected function getBuffer(): string
    {
        return $this->getStreamFilterBuffer();
    }

    protected function resetEnvironment(): void
    {
        putenv('encryption.key');
        unset($_ENV['encryption.key']);
        service('superglobals')->unsetServer('encryption.key');
    }

    public function testGenerateKeyShowsEncodedKey(): void
    {
        command('key:generate --show');
        $this->assertStringContainsString('hex2bin:', $this->getBuffer());

        command('key:generate --prefix base64 --show');
        $this->assertStringContainsString('base64:', $this->getBuffer());

        command('key:generate --prefix hex2bin --show');
        $this->assertStringContainsString('hex2bin:', $this->getBuffer());
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testGenerateKeyCreatesNewKey(): void
    {
        command('key:generate');
        $this->assertStringContainsString('successfully set.', $this->getBuffer());
        $this->assertStringContainsString(env('encryption.key'), (string) file_get_contents($this->envPath));
        $this->assertStringContainsString('hex2bin:', (string) file_get_contents($this->envPath));

        command('key:generate --prefix base64 --force');
        $this->assertStringContainsString('successfully set.', $this->getBuffer());
        $this->assertStringContainsString(env('encryption.key'), (string) file_get_contents($this->envPath));
        $this->assertStringContainsString('base64:', (string) file_get_contents($this->envPath));

        command('key:generate --prefix hex2bin --force');
        $this->assertStringContainsString('successfully set.', $this->getBuffer());
        $this->assertStringContainsString(env('encryption.key'), (string) file_get_contents($this->envPath));
        $this->assertStringContainsString('hex2bin:', (string) file_get_contents($this->envPath));
    }

    public function testDefaultShippedEnvIsMissing(): void
    {
        rename(ROOTPATH . 'env', ROOTPATH . 'lostenv');
        command('key:generate');
        rename(ROOTPATH . 'lostenv', ROOTPATH . 'env');

        $this->assertStringContainsString('Both default shipped', $this->getBuffer());
        $this->assertStringContainsString('Error in setting', $this->getBuffer());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/6838
     */
    public function testKeyGenerateWhenKeyIsMissingInDotEnvFile(): void
    {
        file_put_contents($this->envPath, '');

        command('key:generate');

        $this->assertStringContainsString('Application\'s new encryption key was successfully set.', $this->getBuffer());
        $this->assertSame("\nencryption.key = " . env('encryption.key'), file_get_contents($this->envPath));
    }

    public function testKeyGenerateWhenNewHexKeyIsSubsequentlyCommentedOut(): void
    {
        command('key:generate');
        $key = env('encryption.key', '');
        file_put_contents($this->envPath, str_replace(
            'encryption.key = ' . $key,
            '# encryption.key = ' . $key,
            file_get_contents($this->envPath),
            $count,
        ));
        $this->assertSame(1, $count, 'Failed commenting out the previously set application key.');

        CITestStreamFilter::$buffer = '';
        command('key:generate --force');
        $this->assertStringContainsString('was successfully set.', $this->getBuffer());
        $this->assertNotSame($key, env('encryption.key', $key), 'Failed replacing the commented out key.');
    }

    public function testKeyGenerateWhenNewBase64KeyIsSubsequentlyCommentedOut(): void
    {
        command('key:generate --prefix base64');
        $key = env('encryption.key', '');
        file_put_contents($this->envPath, str_replace(
            'encryption.key = ' . $key,
            '# encryption.key = ' . $key,
            file_get_contents($this->envPath),
            $count,
        ));
        $this->assertSame(1, $count, 'Failed commenting out the previously set application key.');

        CITestStreamFilter::$buffer = '';
        command('key:generate --force');
        $this->assertStringContainsString('was successfully set.', $this->getBuffer());
        $this->assertNotSame($key, env('encryption.key', $key), 'Failed replacing the commented out key.');
    }

    /**
     * Simulates a stale env cache: the `.env` file has a valid key, but
     * `env('encryption.key')` resolves to '' because nothing has loaded it
     * into the superglobals. The primary regex (built from `oldKey`) cannot
     * locate the line, so the fallback regex must replace the existing entry.
     */
    public function testKeyGenerateReplacesUnloadedKeyInDotEnvFile(): void
    {
        $existingKey = 'hex2bin:' . str_repeat('a', 64);
        file_put_contents($this->envPath, "encryption.key = {$existingKey}\n");

        $this->assertSame('', env('encryption.key', ''));

        command('key:generate --force');

        $this->assertStringContainsString('was successfully set.', $this->getBuffer());

        $contents = (string) file_get_contents($this->envPath);
        $this->assertStringNotContainsString($existingKey, $contents);
        $this->assertStringContainsString('encryption.key = ' . env('encryption.key'), $contents);
    }

    public function testKeyGenerateAbortsWhenOverwritePromptIsDeclined(): void
    {
        command('key:generate');
        $key = env('encryption.key', '');
        $this->assertNotSame('', $key);

        $io = new MockInputOutput();
        $io->setInputs(['n']);
        CLI::setInputOutput($io);

        command('key:generate');

        $this->assertSame($key, env('encryption.key', ''), 'Existing key should not change.');
        $this->assertStringContainsString($key, (string) file_get_contents($this->envPath));
        $this->assertStringContainsString('Overwrite existing key?', $io->getOutput());
        $this->assertStringContainsString('Setting new encryption key aborted.', $io->getOutput());
    }

    public function testKeyGenerateOverwritesWhenOverwritePromptIsConfirmed(): void
    {
        command('key:generate');
        $oldKey = env('encryption.key', '');
        $this->assertNotSame('', $oldKey);

        $io = new MockInputOutput();
        $io->setInputs(['y']);
        CLI::setInputOutput($io);

        command('key:generate --prefix base64');

        $this->assertNotSame($oldKey, env('encryption.key', $oldKey));
        $this->assertStringContainsString('base64:', (string) file_get_contents($this->envPath));
        $this->assertStringContainsString('Overwrite existing key?', $io->getOutput());
        $this->assertStringContainsString('successfully set.', $io->getOutput());
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testKeyGenerateAbortsNonInteractivelyWithExistingKey(): void
    {
        command('key:generate');
        $key = env('encryption.key', '');
        $this->assertNotSame('', $key);

        $this->resetStreamFilterBuffer();

        command('key:generate --no-interaction');

        $this->assertSame($key, env('encryption.key', ''), 'Existing key should not change.');
        $this->assertStringContainsString('Setting new encryption key aborted.', $this->getBuffer());
        $this->assertStringContainsString('--force', $this->getBuffer());
    }

    public function testKeyGenerateErrorsOnInvalidPrefixNonInteractively(): void
    {
        command('key:generate --prefix invalid --show --no-interaction');

        $this->assertStringContainsString('Invalid prefix "invalid"', $this->getBuffer());
    }

    public function testKeyGeneratePromptsForInvalidPrefix(): void
    {
        $io = new MockInputOutput();
        $io->setInputs(['hex2bin']);
        CLI::setInputOutput($io);

        command('key:generate --prefix invalid --show');

        $this->assertStringContainsString('Please provide a valid prefix to use.', $io->getOutput());
        $this->assertStringContainsString('hex2bin:', $io->getOutput());
    }
}
