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

namespace CodeIgniter\HotReloader;

use CodeIgniter\Config\Factories;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Toolbar;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class DirectoryHasherTest extends CIUnitTestCase
{
    private DirectoryHasher $hasher;
    private string $fixtureDirectory;
    private string $fixturePath;
    private string $fixturePathAlt;

    protected function setUp(): void
    {
        parent::setUp();

        $suffix                 = str_replace('.', '', uniqid('', true));
        $this->fixtureDirectory = 'writable/hot-reloader-test-' . $suffix;
        $fixtureDirectoryAlt    = 'writable/hot-reloader-test-alt-' . $suffix;
        $this->fixturePath      = ROOTPATH . $this->fixtureDirectory . '/';
        $this->fixturePathAlt   = ROOTPATH . $fixtureDirectoryAlt . '/';

        $this->createFixtureDirectory($this->fixturePath, 'test');
        $this->createFixtureDirectory($this->fixturePathAlt, 'test-alt');

        $this->hasher = new DirectoryHasher();
    }

    protected function tearDown(): void
    {
        $this->removeFixtureDirectory($this->fixturePath);
        $this->removeFixtureDirectory($this->fixturePathAlt);

        parent::tearDown();
    }

    public function testHashApp(): void
    {
        $config                     = new Toolbar();
        $config->watchedDirectories = [$this->fixtureDirectory];
        Factories::injectMock('config', Toolbar::class, $config);

        $results = $this->hasher->hashApp();

        $this->assertIsArray($results);
        $this->assertArrayHasKey($this->fixtureDirectory, $results);
    }

    public function testHashDirectoryInvalid(): void
    {
        $this->expectException(FrameworkException::class);
        $this->expectExceptionMessage('Directory does not exist: "' . APPPATH . 'Foo"');

        $this->hasher->hashDirectory(APPPATH . 'Foo');
    }

    public function testUniqueHashes(): void
    {
        $hash1 = $this->hasher->hashDirectory($this->fixturePath);
        $hash2 = $this->hasher->hashDirectory($this->fixturePathAlt);

        $this->assertNotSame($hash1, $hash2);
    }

    public function testRepeatableHashes(): void
    {
        $hash1 = $this->hasher->hashDirectory($this->fixturePath);
        $hash2 = $this->hasher->hashDirectory($this->fixturePath);

        $this->assertSame($hash1, $hash2);
    }

    public function testHash(): void
    {
        $config                     = new Toolbar();
        $config->watchedDirectories = [$this->fixtureDirectory];
        Factories::injectMock('config', Toolbar::class, $config);

        $expected = md5(implode('', $this->hasher->hashApp()));

        $this->assertSame($expected, $this->hasher->hash());
    }

    private function createFixtureDirectory(string $path, string $contents): void
    {
        if (! is_dir($path)) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path . 'index.php', $contents);
    }

    private function removeFixtureDirectory(string $path): void
    {
        if (is_file($path . 'index.php')) {
            unlink($path . 'index.php');
        }

        if (is_dir($path)) {
            rmdir($path);
        }
    }
}
