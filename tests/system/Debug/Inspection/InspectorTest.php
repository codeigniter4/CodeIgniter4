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

namespace CodeIgniter\Debug\Inspection;

use CodeIgniter\Test\CIUnitTestCase;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use TypeError;

/**
 * @internal
 */
#[CoversClass(Inspector::class)]
#[Group('Others')]
final class InspectorTest extends CIUnitTestCase
{
    public function testChainPreservesOrderAndThrowableIdentity(): void
    {
        $root      = new TypeError('Root cause');
        $middle    = new LogicException('Intermediate exception', previous: $root);
        $outer     = new RuntimeException('Reported exception', previous: $middle);
        $inspector = new Inspector($outer);

        $this->assertSame([$outer, $middle, $root], $inspector->chain());
        $this->assertSame($root, $inspector->rootCause());
    }

    public function testUnchainedExceptionIsItsOwnRootCause(): void
    {
        $exception = new RuntimeException('Single exception');
        $inspector = new Inspector($exception);

        $this->assertSame([$exception], $inspector->chain());
        $this->assertSame($exception, $inspector->rootCause());
    }
}
