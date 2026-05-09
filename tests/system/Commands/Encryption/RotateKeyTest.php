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
use CodeIgniter\Config\DotEnv;
use CodeIgniter\Config\Services;
use CodeIgniter\Superglobals;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockInputOutput;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;

/**
 * @internal
 */
#[Group('Others')]
final class RotateKeyTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private const SEED_KEY = 'hex2bin:0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';

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
        $this->resetServices();

        CLI::reset();
    }

    private function getUndecoratedBuffer(): string
    {
        return preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()) ?? '';
    }

    private static function getUndecoratedIoOutput(MockInputOutput $io): string
    {
        return preg_replace('/\e\[[^m]+m/', '', $io->getOutput()) ?? '';
    }

    private function resetEnvironment(): void
    {
        putenv('encryption.key');
        putenv('encryption.previousKeys');
        unset($_ENV['encryption.key'], $_ENV['encryption.previousKeys']);

        $superglobals = service('superglobals');
        $superglobals->unsetServer('encryption.key');
        $superglobals->unsetServer('encryption.previousKeys');
    }

    private function seedEnv(string $key, string $previousKeys = ''): void
    {
        $content = "encryption.key = {$key}\n";

        if ($previousKeys !== '') {
            $content .= "encryption.previousKeys = {$previousKeys}\n";
        }

        file_put_contents($this->envPath, $content);

        $this->resetEnvironment();
        (new DotEnv(ROOTPATH))->load();
    }

    public function testRotateMovesCurrentKeyToPreviousKeysAndGeneratesNew(): void
    {
        $this->seedEnv(self::SEED_KEY);

        command('key:rotate --force');

        $this->assertSame(
            <<<'EOT'
                Encryption key rotated. 1 previous key retained for decryption fallback.
                Re-encrypt existing data with the new key when ready.

                EOT,
            $this->getUndecoratedBuffer(),
        );

        $contents = (string) file_get_contents($this->envPath);
        $this->assertMatchesRegularExpression(
            '/^encryption\.key = hex2bin:[a-f0-9]{64}\nencryption\.previousKeys = ' . preg_quote(self::SEED_KEY, '/') . '$/m',
            $contents,
            'previousKeys should be inserted on the line directly after encryption.key.',
        );
        $this->assertNotSame(self::SEED_KEY, env('encryption.key'));
        $this->assertSame(self::SEED_KEY, env('encryption.previousKeys'));
    }

    public function testRotatePrependsToExistingPreviousKeysList(): void
    {
        $older  = 'hex2bin:' . str_repeat('a', 64);
        $oldest = 'hex2bin:' . str_repeat('b', 64);
        $this->seedEnv(self::SEED_KEY, "{$older},{$oldest}");

        command('key:rotate --force');

        $this->assertSame(
            <<<'EOT'
                Encryption key rotated. 3 previous keys retained for decryption fallback.
                Re-encrypt existing data with the new key when ready.

                EOT,
            $this->getUndecoratedBuffer(),
        );

        $this->assertSame(
            self::SEED_KEY . ",{$older},{$oldest}",
            env('encryption.previousKeys'),
        );
    }

    public function testRotateDeduplicatesWhenCurrentKeyAlreadyInPreviousKeys(): void
    {
        $other = 'hex2bin:' . str_repeat('a', 64);
        $this->seedEnv(self::SEED_KEY, self::SEED_KEY . ",{$other}");

        command('key:rotate --force');

        $contents = (string) file_get_contents($this->envPath);
        $this->assertSame(
            self::SEED_KEY . ",{$other}",
            env('encryption.previousKeys'),
            'Current key should not appear twice in the rotated list.',
        );
        $this->assertSame(
            1,
            substr_count($contents, 'encryption.previousKeys = '),
            'Should rewrite the previousKeys line in place rather than appending a duplicate.',
        );
        $this->assertStringNotContainsString(
            "\n\nencryption.previousKeys",
            $contents,
            'In-place replacement should not introduce a blank line before encryption.previousKeys.',
        );
    }

    public function testRotateRespectsKeepLimit(): void
    {
        $a = 'hex2bin:' . str_repeat('a', 64);
        $b = 'hex2bin:' . str_repeat('b', 64);
        $c = 'hex2bin:' . str_repeat('c', 64);
        $this->seedEnv(self::SEED_KEY, "{$a},{$b},{$c}");

        command('key:rotate --force --keep=2');

        $this->assertSame(
            self::SEED_KEY . ",{$a}",
            env('encryption.previousKeys'),
        );
        $contents = (string) file_get_contents($this->envPath);
        $this->assertStringNotContainsString($b, $contents);
        $this->assertStringNotContainsString($c, $contents);
    }

    public function testRotateRespectsKeepLimitOfOne(): void
    {
        $older  = 'hex2bin:' . str_repeat('a', 64);
        $oldest = 'hex2bin:' . str_repeat('b', 64);
        $this->seedEnv(self::SEED_KEY, "{$older},{$oldest}");

        command('key:rotate --force --keep=1');

        $this->assertSame(self::SEED_KEY, env('encryption.previousKeys'));
        $contents = (string) file_get_contents($this->envPath);
        $this->assertStringNotContainsString($older, $contents);
        $this->assertStringNotContainsString($oldest, $contents);
    }

    public function testRotateErrorsWhenNoCurrentKey(): void
    {
        file_put_contents($this->envPath, "# encryption.key =\n");
        $this->resetEnvironment();
        (new DotEnv(ROOTPATH))->load();

        command('key:rotate --force');

        $this->assertSame(
            <<<'EOT'

                No existing `encryption.key` to rotate. Run `spark key:generate` first.

                EOT,
            $this->getUndecoratedBuffer(),
        );
        $this->assertStringNotContainsString('encryption.previousKeys', (string) file_get_contents($this->envPath));
    }

    public function testRotateCancelsWhenOverwritePromptIsDeclined(): void
    {
        $this->seedEnv(self::SEED_KEY);

        $io = new MockInputOutput();
        $io->setInputs(['n']);
        CLI::setInputOutput($io);

        command('key:rotate');

        $this->assertSame(
            <<<'EOT'
                Rotate encryption key? The current key will be moved to `previousKeys`. [n, y]: n
                Key rotation cancelled.

                EOT,
            self::getUndecoratedIoOutput($io),
        );
        $this->assertSame(self::SEED_KEY, env('encryption.key'));
        $this->assertStringContainsString(self::SEED_KEY, (string) file_get_contents($this->envPath));
    }

    public function testRotateOverwritesWhenOverwritePromptIsConfirmed(): void
    {
        $this->seedEnv(self::SEED_KEY);

        $io = new MockInputOutput();
        $io->setInputs(['y']);
        CLI::setInputOutput($io);

        command('key:rotate --prefix base64');

        $this->assertSame(
            <<<'EOT'
                Rotate encryption key? The current key will be moved to `previousKeys`. [n, y]: y
                Encryption key rotated. 1 previous key retained for decryption fallback.
                Re-encrypt existing data with the new key when ready.

                EOT,
            self::getUndecoratedIoOutput($io),
        );
        $this->assertNotSame(self::SEED_KEY, env('encryption.key'));
        $this->assertSame(self::SEED_KEY, env('encryption.previousKeys'));
    }

    public function testRotateAbortsNonInteractively(): void
    {
        $this->seedEnv(self::SEED_KEY);

        command('key:rotate --no-interaction');

        $this->assertSame(
            <<<'EOT'

                Key rotation aborted: pass --force to rotate the encryption key in non-interactive mode.

                EOT,
            $this->getUndecoratedBuffer(),
        );
        $this->assertSame(self::SEED_KEY, env('encryption.key'));
    }

    public function testRotateWithBase64Prefix(): void
    {
        $this->seedEnv(self::SEED_KEY);

        command('key:rotate --prefix base64 --force');

        $contents = (string) file_get_contents($this->envPath);
        $this->assertMatchesRegularExpression('/^encryption\.key = base64:[A-Za-z0-9+\/]+={0,2}$/m', $contents);
        $this->assertSame(self::SEED_KEY, env('encryption.previousKeys'));
    }

    public function testRotateErrorsOnInvalidPrefixNonInteractively(): void
    {
        $this->seedEnv(self::SEED_KEY);

        command('key:rotate --prefix invalid --no-interaction');

        $this->assertSame(
            <<<'EOT'

                Invalid prefix "invalid". Use either "hex2bin" or "base64".

                EOT,
            $this->getUndecoratedBuffer(),
        );
        $this->assertSame(self::SEED_KEY, env('encryption.key'));
    }

    public function testRotateInteractRePromptsForInvalidPrefix(): void
    {
        $this->seedEnv(self::SEED_KEY);

        $io = new MockInputOutput();
        // First input answers the invalid-prefix recovery prompt; second answers the rotate confirmation.
        $io->setInputs(['base64', 'y']);
        CLI::setInputOutput($io);

        command('key:rotate --prefix invalid');

        $output = self::getUndecoratedIoOutput($io);
        $this->assertStringContainsString('Please provide a valid prefix to use. [hex2bin, base64]: base64', $output);
        $this->assertStringContainsString('Encryption key rotated. 1 previous key retained for decryption fallback.', $output);

        $contents = (string) file_get_contents($this->envPath);
        $this->assertMatchesRegularExpression('/^encryption\.key = base64:[A-Za-z0-9+\/]+={0,2}$/m', $contents);
        $this->assertSame(self::SEED_KEY, env('encryption.previousKeys'));
    }

    public function testRotateInteractSkipsConfirmationWhenNoCurrentKey(): void
    {
        file_put_contents($this->envPath, "# encryption.key =\n");
        $this->resetEnvironment();
        (new DotEnv(ROOTPATH))->load();

        // No MockInputOutput inputs are set; if interact() reached the rotate prompt it would
        // throw `LogicException('No input data...')` from `MockInputOutput::input()`.
        $io = new MockInputOutput();
        CLI::setInputOutput($io);

        command('key:rotate');

        $this->assertSame(
            <<<'EOT'

                No existing `encryption.key` to rotate. Run `spark key:generate` first.

                EOT,
            self::getUndecoratedIoOutput($io),
        );
    }

    public function testRotateRejectsNegativeKeepValue(): void
    {
        $this->seedEnv(self::SEED_KEY);

        command('key:rotate --force --keep=-1');

        $this->assertSame(
            <<<'EOT'

                The --keep option must be a non-negative integer.

                EOT,
            $this->getUndecoratedBuffer(),
        );
        $this->assertSame(self::SEED_KEY, env('encryption.key'));
    }

    public function testRotateRejectsNonNumericKeepValue(): void
    {
        $this->seedEnv(self::SEED_KEY);

        command('key:rotate --force --keep=abc');

        $this->assertSame(
            <<<'EOT'

                The --keep option must be a non-negative integer.

                EOT,
            $this->getUndecoratedBuffer(),
        );
        $this->assertSame(self::SEED_KEY, env('encryption.key'));
    }

    public function testRotateRejectsNegativeLengthValue(): void
    {
        $this->seedEnv(self::SEED_KEY);
        $envContentsBefore = (string) file_get_contents($this->envPath);

        command('key:rotate --force --length=-1');

        $this->assertSame(
            <<<'EOT'

                The --length option must be a positive integer.

                EOT,
            $this->getUndecoratedBuffer(),
        );
        $this->assertSame(self::SEED_KEY, env('encryption.key'));
        $this->assertSame(
            $envContentsBefore,
            (string) file_get_contents($this->envPath),
            'Validation must reject the run before any .env mutation.',
        );
    }

    public function testRotateRejectsZeroLengthValue(): void
    {
        $this->seedEnv(self::SEED_KEY);
        $envContentsBefore = (string) file_get_contents($this->envPath);

        command('key:rotate --force --length=0');

        $this->assertSame(
            <<<'EOT'

                The --length option must be a positive integer.

                EOT,
            $this->getUndecoratedBuffer(),
        );
        $this->assertSame(self::SEED_KEY, env('encryption.key'));
        $this->assertSame($envContentsBefore, (string) file_get_contents($this->envPath));
    }

    public function testRotateRejectsNonNumericLengthValue(): void
    {
        $this->seedEnv(self::SEED_KEY);
        $envContentsBefore = (string) file_get_contents($this->envPath);

        command('key:rotate --force --length=abc');

        $this->assertSame(
            <<<'EOT'

                The --length option must be a positive integer.

                EOT,
            $this->getUndecoratedBuffer(),
        );
        $this->assertSame(self::SEED_KEY, env('encryption.key'));
        $this->assertSame($envContentsBefore, (string) file_get_contents($this->envPath));
    }

    public function testRotateIgnoresCommentMentioningPreviousKeysWhenInserting(): void
    {
        $envContents = "# encryption.previousKeys is for decryption fallback\nencryption.key = " . self::SEED_KEY . "\n";
        file_put_contents($this->envPath, $envContents);
        $this->resetEnvironment();
        (new DotEnv(ROOTPATH))->load();

        command('key:rotate --force');

        $contents = (string) file_get_contents($this->envPath);
        $this->assertMatchesRegularExpression(
            '/^encryption\.previousKeys = ' . preg_quote(self::SEED_KEY, '/') . '$/m',
            $contents,
            'A real `encryption.previousKeys` setting must be written even when a comment mentions the name.',
        );
        $this->assertSame(self::SEED_KEY, env('encryption.previousKeys'));
    }

    public function testRotateReplacesPreviousKeysLineWithExportPrefix(): void
    {
        $existing    = 'hex2bin:' . str_repeat('a', 64);
        $envContents = 'encryption.key = ' . self::SEED_KEY . "\nexport encryption.previousKeys = {$existing}\n";
        file_put_contents($this->envPath, $envContents);
        $this->resetEnvironment();
        (new DotEnv(ROOTPATH))->load();

        command('key:rotate --force');

        $contents = (string) file_get_contents($this->envPath);
        $this->assertMatchesRegularExpression(
            '/^export encryption\.previousKeys = ' . preg_quote(self::SEED_KEY . ',' . $existing, '/') . '$/m',
            $contents,
            'The existing `export` prefix should be preserved and the value rewritten.',
        );
        $this->assertSame(
            self::SEED_KEY . ',' . $existing,
            env('encryption.previousKeys'),
        );
    }

    public function testRotateInsertsAfterExportPrefixedEncryptionKey(): void
    {
        $envContents = 'export encryption.key = ' . self::SEED_KEY . "\n";
        file_put_contents($this->envPath, $envContents);
        $this->resetEnvironment();
        (new DotEnv(ROOTPATH))->load();

        command('key:rotate --force');

        $contents = (string) file_get_contents($this->envPath);
        $this->assertMatchesRegularExpression(
            '/^export encryption\.key = hex2bin:[a-f0-9]{64}\nencryption\.previousKeys = ' . preg_quote(self::SEED_KEY, '/') . '$/m',
            $contents,
            '`encryption.previousKeys` should be inserted on the line directly after an `export`-prefixed `encryption.key`.',
        );
        $this->assertSame(self::SEED_KEY, env('encryption.previousKeys'));
    }

    public function testRotateErrorsWhenEnvFileIsNotWritable(): void
    {
        $this->seedEnv(self::SEED_KEY);
        chmod($this->envPath, 0o444);

        try {
            command('key:rotate --force');

            $this->assertSame(
                sprintf(
                    <<<'EOT'

                        Failed to write `encryption.previousKeys` to %s.

                        EOT,
                    clean_path($this->envPath),
                ),
                $this->getUndecoratedBuffer(),
            );
            $this->assertSame(self::SEED_KEY, env('encryption.key'));
        } finally {
            chmod($this->envPath, 0o644);
        }
    }
}
