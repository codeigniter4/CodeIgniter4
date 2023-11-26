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

use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
abstract class AbstractHandlerTest extends CIUnitTestCase
{
    protected BaseHandler $handler;
    protected static $key1  = 'key1';
    protected static $key2  = 'key2';
    protected static $key3  = 'key3';
    protected static $dummy = 'dymmy';

    public function testGetMetaDataMiss(): void
    {
        $this->assertNull($this->handler->getMetaData(self::$dummy));
    }

    public function testGetMetaData(): void
    {
        $time = Time::now()->getTimestamp();
        $this->handler->save(self::$key1, 'value');

        $actual = $this->handler->getMetaData(self::$key1);

        // This test is time-dependent, and depending on the timing,
        // seconds in `$time` (e.g. 12:00:00.9999) and seconds of
        // `$this->memcachedHandler->save()` (e.g. 12:00:01.0000)
        // may be off by one second. In that case, the following calculation
        // will result in maximum of (60 + 1).
        $this->assertLessThanOrEqual(60 + 1, $actual['expire'] - $time);

        $this->assertLessThanOrEqual(1, $actual['mtime'] - $time);
        $this->assertSame('value', $actual['data']);
    }
}
