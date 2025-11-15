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

namespace CodeIgniter\Test;

use Config\Logger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class TestLoggerTest extends CIUnitTestCase
{
    #[DataProvider('provideDidLogMethod')]
    public function testDidLogMethod(bool $expected, string $level, string $message, bool $exact): void
    {
        (new TestLogger(new Logger()))->log('error', 'Some variable did not contain a value.');

        $this->assertSame(
            $expected,
            TestLogger::didLog($level, $message, $exact),
        );
    }

    public static function provideDidLogMethod(): iterable
    {
        yield 'exact' => [
            true,
            'error',
            'Some variable did not contain a value.',
            true,
        ];

        yield 'wrong level' => [
            false,
            'warning',
            'Some variable did not contain a value.',
            true,
        ];

        yield 'wrong message' => [
            false,
            'error',
            'Some variables did not contain a value.',
            true,
        ];

        yield 'approximate' => [
            true,
            'error',
            'Some variable did not',
            false,
        ];

        yield 'approximate but wrong level' => [
            false,
            'warning',
            'Some variable did not',
            false,
        ];
    }
}
