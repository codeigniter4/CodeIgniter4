<?php

namespace CodeIgniter;

use CodeIgniter\Exceptions\DebugTraceableTrait;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @covers \CodeIgniter\Exceptions\DebugTraceableTrait
 */
final class DebugTraceableTraitTest extends CIUnitTestCase
{
	public function testFactoryInstanceReturnsWhereItIsRaised(): void
	{
		$e1 = new FrameworkException('I am on line 16');
		$e2 = FrameworkException::forEnabledZlibOutputCompression();

		$this->assertContainsEquals(DebugTraceableTrait::class, class_uses(FrameworkException::class));
		$this->assertSame(16, $e1->getLine());
		$this->assertSame(__FILE__, $e1->getFile());
		$this->assertSame(17, $e2->getLine());
		$this->assertSame(__FILE__, $e2->getFile());
	}
}
