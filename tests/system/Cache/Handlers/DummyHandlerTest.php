<?php

namespace CodeIgniter\Cache\Handlers;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class DummyHandlerTest extends CIUnitTestCase
{
    private $dummyHandler;

    protected function setUp(): void
    {
        $this->dummyHandler = new DummyHandler();
        $this->dummyHandler->initialize();
    }

    public function testNew()
    {
        $this->assertInstanceOf(DummyHandler::class, $this->dummyHandler);
    }

    public function testGet()
    {
        $this->assertNull($this->dummyHandler->get('key'));
    }

    public function testRemember()
    {
        $dummyHandler = $this->dummyHandler->remember('key', 2, static function () {
            return 'value';
        });

        $this->assertNull($dummyHandler);
    }

    public function testSave()
    {
        $this->assertTrue($this->dummyHandler->save('key', 'value'));
    }

    public function testDelete()
    {
        $this->assertTrue($this->dummyHandler->delete('key'));
    }

    public function testDeleteMatching()
    {
        $this->assertSame(0, $this->dummyHandler->deleteMatching('key*'));
    }

    public function testIncrement()
    {
        $this->assertTrue($this->dummyHandler->increment('key'));
    }

    public function testDecrement()
    {
        $this->assertTrue($this->dummyHandler->decrement('key'));
    }

    public function testClean()
    {
        $this->assertTrue($this->dummyHandler->clean());
    }

    public function testGetCacheInfo()
    {
        $this->assertNull($this->dummyHandler->getCacheInfo());
    }

    public function testGetMetaData()
    {
        $this->assertNull($this->dummyHandler->getMetaData('key'));
    }

    public function testIsSupported()
    {
        $this->assertTrue($this->dummyHandler->isSupported());
    }
}
