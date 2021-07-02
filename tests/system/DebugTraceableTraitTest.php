<?php

namespace CodeIgniter;

use CodeIgniter\Exceptions\DebugTraceableTrait;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @covers \CodeIgniter\Exceptions\DebugTraceableTrait
 *
 * @internal
 */
final class DebugTraceableTraitTest extends CIUnitTestCase
{
    public function testFactoryInstanceReturnsWhereItIsRaised(): void
    {
        $e1 = new FrameworkException('I am on line 18');
        $e2 = FrameworkException::forEnabledZlibOutputCompression();

        $this->assertContainsEquals(DebugTraceableTrait::class, class_uses(FrameworkException::class));
        $this->assertSame(18, $e1->getLine());
        $this->assertSame(__FILE__, $e1->getFile());
        $this->assertSame(19, $e2->getLine());
        $this->assertSame(__FILE__, $e2->getFile());
    }
}
