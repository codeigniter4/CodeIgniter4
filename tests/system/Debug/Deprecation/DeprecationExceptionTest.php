<?php

namespace CodeIgniter\Debug\Deprecation;

use CodeIgniter\Test\CIUnitTestCase;
use ErrorException;

/**
 * @internal
 */
final class DeprecationExceptionTest extends CIUnitTestCase
{
	public function testInstance(): void
	{
		$error = new DeprecationException('The deprecation message.');

		$this->assertInstanceOf(DeprecationException::class, $error);
		$this->assertInstanceOf(ErrorException::class, $error);
		$this->assertSame('DEPRECATED: The deprecation message.', $error->getMessage());
		$this->assertSame(0, $error->getCode());
		$this->assertSame(E_USER_DEPRECATED, $error->getSeverity());
	}

	public function testExceptionNormalizesMessage(): void
	{
		$error1 = new DeprecationException('Yes.');
		$error2 = new DeprecationException('DEPRECATED: Yes.');

		$this->assertSame('DEPRECATED: Yes.', $error1->getMessage());
		$this->assertSame('DEPRECATED: Yes.', $error2->getMessage());
		$this->assertSame($error1->getMessage(), $error2->getMessage());
	}
}
