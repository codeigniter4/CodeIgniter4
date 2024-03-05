<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Events\Events;
use Tests\Support\Test\TestForReflectionHelper;

/**
 * @internal
 *
 * @group Others
 */
final class TestCaseTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset CLI::$lastWrite
        CLI::print();
    }

    public function testGetPrivatePropertyWithObject(): void
    {
        $obj    = new TestForReflectionHelper();
        $actual = $this->getPrivateProperty($obj, 'private');
        $this->assertSame('secret', $actual);
    }

    public function testLogging(): void
    {
        log_message('error', 'Some variable did not contain a value.');
        $this->assertLogged('error', 'Some variable did not contain a value.');
    }

    public function testAssertLogContains(): void
    {
        log_message('error', 'Some variable did not contain a value.');
        $this->assertLogContains('error', 'variable did not');
    }

    public function testEventTriggering(): void
    {
        $result = '';

        Events::on('foo', static function ($arg) use (&$result): void {
            $result = $arg;
        });

        Events::trigger('foo', 'bar');

        $this->assertEventTriggered('foo');
        $this->assertSame('bar', $result);
    }

    public function testStreamFilter(): void
    {
        CLI::write('first.');
        $expected = PHP_EOL . 'first.' . PHP_EOL;
        $this->assertSame($expected, $this->getStreamFilterBuffer());
    }

    public function testCloseEnough(): void
    {
        $this->assertCloseEnough(1, 1);
        $this->assertCloseEnough(1, 0);
        $this->assertCloseEnough(1, 2);
    }

    public function testCloseEnoughString(): void
    {
        $this->assertCloseEnoughString(strtotime('10:00:00'), strtotime('09:59:59'));
        $this->assertCloseEnoughString(strtotime('10:00:00'), strtotime('10:00:00'));
        $this->assertCloseEnoughString(strtotime('10:00:00'), strtotime('10:00:01'));
    }

    public function testCloseEnoughStringBadLength(): void
    {
        $result = $this->assertCloseEnoughString('apples & oranges', 'apples');
        $this->assertFalse($result, 'Different string lengths should have returned false');
    }
}
