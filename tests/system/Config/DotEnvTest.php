<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Config;

use CodeIgniter\Test\CIUnitTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * @backupGlobals enabled
 *
 * @internal
 *
 * @group SeparateProcess
 */
final class DotEnvTest extends CIUnitTestCase
{
    private ?vfsStreamDirectory $root;
    private string $path;
    private string $fixturesFolder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root           = vfsStream::setup();
        $this->fixturesFolder = $this->root->url();
        $this->path           = TESTPATH . 'system/Config/fixtures';
        vfsStream::copyFromFileSystem($this->path, $this->root);

        $file = 'unreadable.env';
        $path = rtrim($this->fixturesFolder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
        chmod($path, 0644);
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

    /**
     * @dataProvider provideLoadsVars
     */
    public function testLoadsVars(string $expected, string $varname): void
    {
        $dotenv = new DotEnv($this->fixturesFolder);
        $dotenv->load();

        $this->assertSame($expected, getenv($varname));
    }

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

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLoadsHex2Bin(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, 'encryption.env');
        $dotenv->load();

        $this->assertSame('hex2bin:f699c7fd18a8e082d0228932f3acd40e1ef5ef92efcedda32842a211d62f0aa6', getenv('encryption.key'));
        $this->assertSame('hex2bin:f699c7fd18a8e082d0228932f3acd40e1ef5ef92efcedda32842a211d62f0aa6', getenv('different.key'));
        $this->assertSame('OpenSSL', getenv('encryption.driver'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLoadsBase64(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, 'base64encryption.env');
        $dotenv->load();

        $this->assertSame('base64:L40bKo6b8Nu541LeVeZ1i5RXfGgnkar42CPTfukhGhw=', getenv('encryption.key'));
        $this->assertSame('OpenSSL', getenv('encryption.driver'));
    }

    public function testLoadsNoneStringFiles(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, 2);
        $dotenv->load();
        $this->assertSame('bar', getenv('FOO'));
        $this->assertSame('baz', getenv('BAR'));
        $this->assertSame('with spaces', getenv('SPACED'));
        $this->assertSame('', getenv('NULL'));
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

        $this->assertSame('bar', $_SERVER['FOO']);
        $this->assertSame('baz', $_SERVER['BAR']);
        $this->assertSame('with spaces', $_SERVER['SPACED']);
        $this->assertSame('', $_SERVER['NULL']);
    }

    public function testNamespacedVariables(): void
    {
        $dotenv = new DotEnv($this->fixturesFolder, '.env');
        $dotenv->load();

        $this->assertSame('complex', $_SERVER['SimpleConfig_simple_name']);
    }

    public function testLoadsGetServerVar(): void
    {
        $_SERVER['SER_VAR'] = 'TT';
        $dotenv             = new DotEnv($this->fixturesFolder, 'nested.env');
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
