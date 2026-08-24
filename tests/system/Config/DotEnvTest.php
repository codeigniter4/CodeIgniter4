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

namespace CodeIgniter\Config;

use CodeIgniter\Superglobals;
use CodeIgniter\Test\CIUnitTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;

/**
 * @internal
 */
#[BackupGlobals(true)]
#[Group('SeparateProcess')]
final class DotEnvTest extends CIUnitTestCase
{
    private ?vfsStreamDirectory $root;
    private string $fixturesFolder;

    #[WithoutErrorHandler]
    protected function setUp(): void
    {
        parent::setUp();

        $this->root           = vfsStream::setup();
        $this->fixturesFolder = $this->root->url();
        $path                 = TESTPATH . 'system/Config/fixtures';
        vfsStream::copyFromFileSystem($path, $this->root);

        $file = 'unreadable.env';
        $path = rtrim($this->fixturesFolder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
        chmod($path, 0644);

        Services::injectMock('superglobals', new Superglobals());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->root = null;
    }

    public function testReturnsFalseIfCannotFindFile(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, 'bogus');
        $this->assertFalse($dotenv->load());
    }

    #[DataProvider('provideLoadsVars')]
    public function testLoadsVars(string $expected, string $varname): void
    {
        $dotenv = new DotEnv($this->fixturesFolder);
        $dotenv->load();

        $this->assertSame($expected, getenv($varname));
    }

    /**
     * @return iterable<array{string, string}>
     */
    public static function provideLoadsVars(): iterable
    {
        yield from [
            ['bar', 'FOO'],
            ['baz', 'BAR'],
            ['with spaces', 'SPACED'],
            ['', 'NULL'],
            ['exported foo', 'char.expo.foo'],
            ['variable', 'character.export.var'],
            ['character', 'char.var'],
            ['imports', 'char.exports'],
            ['banana', 'fruit.export'],
        ];
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testLoadsHex2Bin(): void
    {
        putenv('encryption.key');
        unset($_ENV['encryption.key']);
        service('superglobals')->unsetServer('encryption.key');

        $dotenv = new DotEnv($this->fixturesFolder, 'encryption.env');
        $dotenv->load();

        $this->assertSame('hex2bin:f699c7fd18a8e082d0228932f3acd40e1ef5ef92efcedda32842a211d62f0aa6', getenv('encryption.key'));
        $this->assertSame('hex2bin:f699c7fd18a8e082d0228932f3acd40e1ef5ef92efcedda32842a211d62f0aa6', getenv('different.key'));
        $this->assertSame('OpenSSL', getenv('encryption.driver'));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testLoadsBase64(): void
    {
        putenv('encryption.key');
        unset($_ENV['encryption.key']);
        service('superglobals')->unsetServer('encryption.key');

        $dotenv = new DotEnv($this->fixturesFolder, 'base64encryption.env');
        $dotenv->load();

        $this->assertSame('base64:L40bKo6b8Nu541LeVeZ1i5RXfGgnkar42CPTfukhGhw=', getenv('encryption.key'));
        $this->assertSame('OpenSSL', getenv('encryption.driver'));
    }

    public function testCommentedLoadsVars(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, 'commented.env');
        $dotenv->load();
        $this->assertSame('bar', getenv('CFOO'));
        $this->assertFalse(getenv('CBAR'));
        $this->assertFalse(getenv('CZOO'));
        $this->assertSame('with spaces', getenv('CSPACED'));
        $this->assertSame('a value with a # character', getenv('CQUOTES'));
        $this->assertSame('a value with a # character & a quote " character inside quotes', getenv('CQUOTESWITHQUOTE'));
        $this->assertSame('', getenv('CNULL'));
    }

    public function testLoadsUnreadableFile(): void
    {
        $file = 'unreadable.env';
        $path = rtrim($this->fixturesFolder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
        chmod($path, 0000);
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage("The .env file is not readable: {$path}");
        $dotenv = new DotEnv($this->fixturesFolder, $file);
        $dotenv->load();
    }

    /**
     * Regression test: a concurrent process may remove or replace the .env
     * file between the is_file() check and the read attempt. In that case the
     * file must be treated as absent (parse() returns null) instead of
     * throwing an exception.
     */
    public function testParseReturnsNullIfFileRemovedBetweenCheckAndRead(): void
    {
        $scheme = 'vanishenv';

        $wrapper = new class () {
            public static bool $vanished = false;

            /**
             * @var resource|null
             */
            public $context;

            /**
             * @return array<string, int>|false
             */
            public function url_stat(string $path, int $flags): array|false
            {
                if (self::$vanished) {
                    return false;
                }

                return [
                    'dev'     => 0,
                    'ino'     => 0,
                    'mode'    => 0100644,
                    'nlink'   => 1,
                    'uid'     => 0,
                    'gid'     => 0,
                    'rdev'    => 0,
                    'size'    => 1,
                    'atime'   => 1,
                    'mtime'   => 1,
                    'ctime'   => 1,
                    'blksize' => 4096,
                    'blocks'  => 8,
                ];
            }

            public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
            {
                self::$vanished = true;

                return false;
            }
        };

        stream_wrapper_register($scheme, $wrapper::class, STREAM_IS_URL);

        try {
            $dotenv = new DotEnv("{$scheme}://dir", '.env');
            $this->assertNull($dotenv->parse());
        } finally {
            stream_wrapper_unregister($scheme);
        }
    }

    public function testQuotedDotenvLoadsEnvironmentVars(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, 'quoted.env');
        $dotenv->load();
        $this->assertSame('bar', getenv('QFOO'));
        $this->assertSame('baz', getenv('QBAR'));
        $this->assertSame('with spaces', getenv('QSPACED'));
        $this->assertSame('', getenv('QNULL'));
        $this->assertSame('pgsql:host=localhost;dbname=test', getenv('QEQUALS'));
        $this->assertSame('test some escaped characters like a quote (") or maybe a backslash (\\)', getenv('QESCAPED'));
    }

    public function testSpacedValuesWithoutQuotesThrowsException(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('.env values containing spaces must be surrounded by quotes.');

        $dotenv = new DotEnv($this->fixturesFolder, 'spaced-wrong.env');
        $dotenv->load();
    }

    public function testLoadsServerGlobals(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, '.env');
        $dotenv->load();

        $this->assertSame('bar', $_SERVER['FOO']); // @phpstan-ignore codeigniter.superglobalsOffsetAccess (checks the live superglobal, not the snapshot service)
        $this->assertSame('baz', $_SERVER['BAR']); // @phpstan-ignore codeigniter.superglobalsOffsetAccess (checks the live superglobal, not the snapshot service)
        $this->assertSame('with spaces', $_SERVER['SPACED']); // @phpstan-ignore codeigniter.superglobalsOffsetAccess (checks the live superglobal, not the snapshot service)
        $this->assertSame('', $_SERVER['NULL']); // @phpstan-ignore codeigniter.superglobalsOffsetAccess (checks the live superglobal, not the snapshot service)
    }

    public function testNamespacedVariables(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, '.env');
        $dotenv->load();

        $this->assertSame('complex', $_SERVER['SimpleConfig_simple_name']); // @phpstan-ignore codeigniter.superglobalsOffsetAccess (checks the live superglobal, not the snapshot service)
    }

    public function testLoadsGetServerVar(): void
    {
        service('superglobals')->setServer('SER_VAR', 'TT');
        $dotenv = new DotEnv($this->fixturesFolder, 'nested.env');
        $dotenv->load();

        $this->assertSame('TT', $_ENV['NVAR7']);
    }

    public function testLoadsEnvGlobals(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder);
        $dotenv->load();
        $this->assertSame('bar', $_ENV['FOO']);
        $this->assertSame('baz', $_ENV['BAR']);
        $this->assertSame('with spaces', $_ENV['SPACED']);
        $this->assertSame('', $_ENV['NULL']);
    }

    public function testNestedEnvironmentVars(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, 'nested.env');
        $dotenv->load();
        $this->assertSame('{$NVAR1} {$NVAR2}', $_ENV['NVAR3']); // not resolved
        $this->assertSame('Hello World!', $_ENV['NVAR4']);
        $this->assertSame('$NVAR1 {NVAR2}', $_ENV['NVAR5']); // not resolved
        $this->assertSame('Hello/World!', $_ENV['NVAR8']);
    }

    public function testDotenvAllowsSpecialCharacters(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, 'specialchars.env');
        $dotenv->load();
        $this->assertSame('$a6^C7k%zs+e^.jvjXk', getenv('SPVAR1'));
        $this->assertSame('?BUty3koaV3%GA*hMAwH}B', getenv('SPVAR2'));
        $this->assertSame('jdgEB4{QgEC]HL))&GcXxokB+wqoN+j>xkV7K?m$r', getenv('SPVAR3'));
        $this->assertSame('22222:22#2^{', getenv('SPVAR4'));
        $this->assertSame('test some escaped characters like a quote " or maybe a backslash \\', getenv('SPVAR5'));
    }
}
