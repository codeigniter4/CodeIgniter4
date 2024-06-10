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

namespace CodeIgniter;

use CodeIgniter\Exceptions\DebugTraceableTrait;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[CoversClass(DebugTraceableTrait::class)]
#[Group('Others')]
final class DebugTraceableTraitTest extends CIUnitTestCase
{
    public function testFactoryInstanceReturnsWhereItIsRaised(): void
    {
        $e1 = new FrameworkException('Hello.');
        $e2 = FrameworkException::forEnabledZlibOutputCompression();

        $this->assertContainsEquals(DebugTraceableTrait::class, class_uses(FrameworkException::class));
        $this->assertSame(__LINE__ - 4, $e1->getLine());
        $this->assertSame(__FILE__, $e1->getFile());
        $this->assertSame(__LINE__ - 5, $e2->getLine());
        $this->assertSame(__FILE__, $e2->getFile());
    }
}
