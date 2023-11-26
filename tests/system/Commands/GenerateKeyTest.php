<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use CodeIgniter\Test\StreamFilterTrait;

/**
 * @internal
 *
 * @group SeparateProcess
 */
final class GenerateKeyTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private string $envPath;
    private string $backupEnvPath;

    protected function setUp(): void
    {
        parent::setUp();

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
        unset($_ENV['encryption.key'], $_SERVER['encryption.key']);
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

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testGenerateKeyCreatesNewKey(): void
    {
        command('key:generate');
        $this->assertStringContainsString('successfully set.', $this->getBuffer());
        $this->assertStringContainsString(env('encryption.key'), file_get_contents($this->envPath));
        $this->assertStringContainsString('hex2bin:', file_get_contents($this->envPath));

        command('key:generate --prefix base64 --force');
        $this->assertStringContainsString('successfully set.', $this->getBuffer());
        $this->assertStringContainsString(env('encryption.key'), file_get_contents($this->envPath));
        $this->assertStringContainsString('base64:', file_get_contents($this->envPath));

        command('key:generate --prefix hex2bin --force');
        $this->assertStringContainsString('successfully set.', $this->getBuffer());
        $this->assertStringContainsString(env('encryption.key'), file_get_contents($this->envPath));
        $this->assertStringContainsString('hex2bin:', file_get_contents($this->envPath));
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
            $count
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
            $count
        ));
        $this->assertSame(1, $count, 'Failed commenting out the previously set application key.');

        CITestStreamFilter::$buffer = '';
        command('key:generate --force');
        $this->assertStringContainsString('was successfully set.', $this->getBuffer());
        $this->assertNotSame($key, env('encryption.key', $key), 'Failed replacing the commented out key.');
    }
}
