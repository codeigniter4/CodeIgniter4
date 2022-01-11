<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Cache\Handlers;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class DummyHandlerTest extends CIUnitTestCase
{
    private DummyHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new DummyHandler();
        $this->handler->initialize();
    }

    public function testNew()
    {
        $this->assertInstanceOf(DummyHandler::class, $this->handler);
    }

    public function testGet()
    {
        $this->assertNull($this->handler->get('key'));
    }

    public function testRemember()
    {
        $dummyHandler = $this->handler->remember('key', 2, static fn () => 'value');

        $this->assertNull($dummyHandler);
    }

    public function testSave()
    {
        $this->assertTrue($this->handler->save('key', 'value'));
    }

    public function testDelete()
    {
        $this->assertTrue($this->handler->delete('key'));
    }

    public function testDeleteMatching()
    {
        $this->assertSame(0, $this->handler->deleteMatching('key*'));
    }

    public function testIncrement()
    {
        $this->assertTrue($this->handler->increment('key'));
    }

    public function testDecrement()
    {
        $this->assertTrue($this->handler->decrement('key'));
    }

    public function testClean()
    {
        $this->assertTrue($this->handler->clean());
    }

    public function testGetCacheInfo()
    {
        $this->assertNull($this->handler->getCacheInfo());
    }

    public function testGetMetaData()
    {
        $this->assertNull($this->handler->getMetaData('key'));
    }

    public function testIsSupported()
    {
        $this->assertTrue($this->handler->isSupported());
    }
}
