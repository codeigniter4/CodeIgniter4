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
 *
 * @group Others
 */
final class DummyHandlerTest extends CIUnitTestCase
{
    private DummyHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new DummyHandler();
        $this->handler->initialize();
    }

    public function testNew(): void
    {
        $this->assertInstanceOf(DummyHandler::class, $this->handler);
    }

    public function testGet(): void
    {
        $this->assertNull($this->handler->get('key'));
    }

    public function testRemember(): void
    {
        $dummyHandler = $this->handler->remember('key', 2, static fn () => 'value');

        $this->assertNull($dummyHandler);
    }

    public function testSave(): void
    {
        $this->assertTrue($this->handler->save('key', 'value'));
    }

    public function testDelete(): void
    {
        $this->assertTrue($this->handler->delete('key'));
    }

    public function testDeleteMatching(): void
    {
        $this->assertSame(0, $this->handler->deleteMatching('key*'));
    }

    public function testIncrement(): void
    {
        $this->assertTrue($this->handler->increment('key'));
    }

    public function testDecrement(): void
    {
        $this->assertTrue($this->handler->decrement('key'));
    }

    public function testClean(): void
    {
        $this->assertTrue($this->handler->clean());
    }

    public function testGetCacheInfo(): void
    {
        $this->assertNull($this->handler->getCacheInfo());
    }

    public function testGetMetaData(): void
    {
        $this->assertNull($this->handler->getMetaData('key'));
    }

    public function testIsSupported(): void
    {
        $this->assertTrue($this->handler->isSupported());
    }
}
