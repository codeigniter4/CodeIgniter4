<?php

namespace Tests\System\HotReloader;

use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\DirectoryHasher;
use CodeIgniter\Test\CIUnitTestCase;

class DirectoryHasherTest extends CIUnitTestCase
{
    protected DirectoryHasher $hasher;

    public function setUp(): void
    {
        parent::setUp();

        $this->hasher = new DirectoryHasher();
    }

    public function testHashApp()
    {
        $results = $this->hasher->hashApp();

        $this->assertIsArray($results);
        $this->assertArrayHasKey('app', $results);
    }

    public function testHashDirectoryInvalid()
    {
        $this->expectException(FrameworkException::class);
        $this->expectExceptionMessage('Directory does not exist: "' . APPPATH . 'Foo"');

        $this->hasher->hashDirectory(APPPATH . 'Foo');
    }

    public function testUniqueHashes()
    {
        $hash1 = $this->hasher->hashDirectory(APPPATH);
        $hash2 = $this->hasher->hashDirectory(SYSTEMPATH);

        $this->assertNotEquals($hash1, $hash2);
    }

    public function testRepeatableHashes()
    {
        $hash1 = $this->hasher->hashDirectory(APPPATH);
        $hash2 = $this->hasher->hashDirectory(APPPATH);

        $this->assertEquals($hash1, $hash2);
    }

    public function testHash()
    {
        $expected = md5(implode('', $this->hasher->hashApp()));

        $this->assertEquals($expected, $this->hasher->hash());
    }
}
