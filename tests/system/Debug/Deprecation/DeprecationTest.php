<?php

namespace CodeIgniter\Debug\Deprecation;

use BadMethodCallException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ReflectionHelper;
use Error;
use LogicException;
use Psr\Log\LogLevel;
use Tests\Support\Debug\Deprecation\DeprecationChecker;
use Tests\Support\Debug\Deprecation\MyClassWithDeprecatedMethods;
use Tests\Support\Debug\Deprecation\MyClassWithDeprecatedProps;
use Tests\Support\Debug\Deprecation\MyDeprecatedClass;
use Tests\Support\Debug\Deprecation\MyDeprecatedInterface;
use Tests\Support\Debug\Deprecation\MyDeprecatedTrait;
use Tests\Support\Debug\Deprecation\MyReplacementClass;
use Throwable;
use UnexpectedValueException;

/**
 * @internal
 */
final class DeprecationTest extends CIUnitTestCase
{
	use ReflectionHelper;

	private $mode = '';

	protected function setUp(): void
	{
		parent::setUp();

		$this->mode = Deprecation::mode();
		Deprecation::setMode(Deprecation::THROW_EXCEPTION);
	}

	protected function tearDown(): void
	{
		parent::tearDown();

		Deprecation::setMode($this->mode);
	}

	public function testModeIsDefined(): void
	{
		$this->assertContains(Deprecation::mode(), Deprecation::SUPPORTED_MODES);
	}

	public function testSettingUnknownModeFails(): void
	{
		$this->expectException(UnexpectedValueException::class);
		$this->expectExceptionMessage('Mode "foo" is not supported. Allowed: "log_message", "throw_exception".');
		Deprecation::setMode('foo');
	}

	public function testSettingModeChangesCurrentMode(): void
	{
		$oldMode = Deprecation::mode();
		Deprecation::setMode(Deprecation::LOG_MESSAGE);

		$this->assertSame(Deprecation::THROW_EXCEPTION, $oldMode);
		$this->assertSame(Deprecation::LOG_MESSAGE, Deprecation::mode());

		Deprecation::setMode($oldMode);
	}

	public function testGenericTrigger(): void
	{
		Deprecation::setMode(Deprecation::LOG_MESSAGE);
		Deprecation::trigger('A deprecation is raised.');
		$this->assertLogged(LogLevel::ERROR, 'DEPRECATED: A deprecation is raised.');

		Deprecation::setMode(Deprecation::THROW_EXCEPTION);
		$this->expectException(DeprecationException::class);
		$this->expectExceptionMessage('DEPRECATED: A deprecation is raised.');
		Deprecation::trigger('A deprecation is raised.');
	}

	public function testTriggerForClass(): void
	{
		$this->expectException(DeprecationException::class);
		new MyDeprecatedClass();
	}

	public function testTriggerForClassOnExtends(): void
	{
		try
		{
			new class extends MyDeprecatedClass {};
		}
		catch (Throwable $e)
		{
			$this->assertInstanceOf(DeprecationException::class, $e);
			Deprecation::setMode(Deprecation::LOG_MESSAGE);
			new class extends MyDeprecatedClass {};
			$this->assertLogged(LogLevel::ERROR, $e->getMessage());
		}
	}

	public function testTriggerForClassWithInvalidReplacement(): void
	{
		$this->expectException(UnexpectedValueException::class);
		new class {
			public function __construct()
			{
				Deprecation::triggerForClass(self::class, 'Foo');
			}
		};
	}

	public function testTriggerForClassWithInvalidName(): void
	{
		$this->expectException(UnexpectedValueException::class);
		new class {
			public function __construct()
			{
				Deprecation::triggerForClass('Foo', MyReplacementClass::class);
			}
		};
	}

	public function testTriggerForInterface(): void
	{
		try
		{
			new class extends DeprecationChecker implements MyDeprecatedInterface {
				public function foo() {}
			};
		}
		catch (Throwable $e)
		{
			$this->assertInstanceOf(DeprecationException::class, $e);
			Deprecation::setMode(Deprecation::LOG_MESSAGE);
			new class extends DeprecationChecker implements MyDeprecatedInterface {
				public function foo() {}
			};
			$this->assertLogged(LogLevel::ERROR, $e->getMessage());
		}
	}

	public function testTriggerForTrait(): void
	{
		try
		{
			new class extends DeprecationChecker { use MyDeprecatedTrait; };
		}
		catch (Throwable $e)
		{
			$this->assertInstanceOf(DeprecationException::class, $e);
			Deprecation::setMode(Deprecation::LOG_MESSAGE);
			new class extends DeprecationChecker { use MyDeprecatedTrait; };
			$this->assertLogged(LogLevel::ERROR, $e->getMessage());
		}
	}

	public function testTriggerForPropertyAccess(): void
	{
		$this->expectException(DeprecationException::class);
		$class = new MyClassWithDeprecatedProps();
		$class->notBarred;
		$class->deprecatedProtected;
	}

	public function testTriggerForPropertyAccessByChild(): void
	{
		$this->expectException(DeprecationException::class);
		$class = new class extends MyClassWithDeprecatedProps {};
		$class->deprecatedPrivate;
	}

	public function testPropertyAccessFailingForInexistentProps(): void
	{
		$this->expectException(LogicException::class);
		$class = new MyClassWithDeprecatedProps();
		$class->unknown;
	}

	public function testPropertyAccessForDisallowedProperties(): void
	{
		$this->expectException(Error::class);
		$class = new MyClassWithDeprecatedProps();
		$class->barred;
	}

	public function testTriggerForPropertyAssignment(): void
	{
		$this->expectException(DeprecationException::class);
		$class = new MyClassWithDeprecatedProps();
		$class->deprecatedProtected = 5;
	}

	public function testTriggerForPropertyAssignmentByChild(): void
	{
		$this->expectException(DeprecationException::class);
		$class = new class extends MyClassWithDeprecatedProps {
			public function foo()
			{
				$this->deprecatedPrivate = 5;
			}
		};
		$class->notBarred = 5;
		$class->foo();
	}

	public function testPropertyAssignmentFailingForUnknownProps(): void
	{
		$this->expectException(LogicException::class);
		$class = new MyClassWithDeprecatedProps();
		$class->unknown = 5;
	}

	public function testPropertyAssignmentFailingForDisallowedProps(): void
	{
		$this->expectException(Error::class);
		$class = new MyClassWithDeprecatedProps();
		$class->barredToo = 5;
	}

	public function testTriggerForPropertySilent(): void
	{
		Deprecation::setMode(Deprecation::LOG_MESSAGE);
		$class = new MyClassWithDeprecatedProps();

		$class->deprecatedProtected;
		$this->assertLogged(LogLevel::ERROR, 'DEPRECATED: ' . lang('Deprecation.propertyAccessDeprecated', ['$deprecatedProtected', MyClassWithDeprecatedProps::class, '$replacement']));

		$class->deprecatedPrivate = 5;
		$this->assertLogged(LogLevel::ERROR, 'DEPRECATED: ' . lang('Deprecation.propertyAssignmentDeprecated', ['$deprecatedPrivate', MyClassWithDeprecatedProps::class]));
	}

	public function testMethodAccessFailForUnknownMethod(): void
	{
		$this->expectException(BadMethodCallException::class);
		$class = new MyClassWithDeprecatedMethods();
		$class->unknown();
	}

	public function testMethodAccessFailForUnknownStaticMethod(): void
	{
		$this->expectException(BadMethodCallException::class);
		MyClassWithDeprecatedMethods::staticUnknown();
	}

	public function testTriggerForMethod(): void
	{
		try
		{
			$class = new MyClassWithDeprecatedMethods();
			$class->explicitlyDeprecated();
		}
		catch (Throwable $e)
		{
			$this->assertInstanceOf(DeprecationException::class, $e);
			Deprecation::setMode(Deprecation::LOG_MESSAGE);
			$class->explicitlyDeprecated();
			$this->assertLogged(LogLevel::ERROR, $e->getMessage());
		}
	}

	public function testMethodAccessOutsideClass(): void
	{
		$this->expectException(DeprecationException::class);
		$class = new MyClassWithDeprecatedMethods();
		$class->implicitlyDeprecatedProtected();
	}

	public function testMethodAccessByChild(): void
	{
		$this->expectException(DeprecationException::class);
		$class = new class extends MyClassWithDeprecatedMethods {};
		$class->implicitlyDeprecatedPrivate();
	}

	public function testMethodAccessBarred(): void
	{
		$this->expectException(Error::class);
		$class = new MyClassWithDeprecatedMethods();
		$class->notBarred();
		$class->barred();
	}

	public function testStaticMethodAccess(): void
	{
		try
		{
			MyClassWithDeprecatedMethods::staticDeprecatedProtected();
		}
		catch (Throwable $e)
		{
			$this->assertInstanceOf(DeprecationException::class, $e);
			Deprecation::setMode(Deprecation::LOG_MESSAGE);
			MyClassWithDeprecatedMethods::staticDeprecatedProtected();
			$this->assertLogged(LogLevel::ERROR, $e->getMessage());
		}
	}

	public function testStaticMethodAccessBarred(): void
	{
		$this->expectException(Error::class);
		MyClassWithDeprecatedMethods::staticNotBarred();
		MyClassWithDeprecatedMethods::staticBarred();
	}

	public function testMethodParameterAccess(): void
	{
		try
		{
			$class = new MyClassWithDeprecatedMethods();
			$class->withDeprecatedParams(1, 2);
		}
		catch (Throwable $e)
		{
			$this->assertInstanceOf(DeprecationException::class, $e);
			Deprecation::setMode(Deprecation::LOG_MESSAGE);
			$class->withDeprecatedParams(1);
			$this->assertLogged(LogLevel::ERROR, $e->getMessage());
		}
	}

	public function testMethodParameterAccessOne(): void
	{
		$this->expectException(DeprecationException::class);
		$class = new MyClassWithDeprecatedMethods();
		$class->withDeprecatedParam(3, true);
	}

	/**
	 * @dataProvider validObjectTypesProvider
	 *
	 * @param boolean $classType
	 * @param boolean $traitType
	 * @param boolean $interfaceType
	 *
	 * @return void
	 */
	public function testValidObjectTypesDeterminationFailing(bool $classType, bool $traitType, bool $interfaceType): void
	{
		$this->expectException(UnexpectedValueException::class);
		$determinator = $this->getPrivateMethodInvoker(Deprecation::class, 'determineIfValidObjectType');
		$determinator('deprecated', 'Foo', $classType, $traitType, $interfaceType);
	}

	public function validObjectTypesProvider(): iterable
	{
		yield 'all-types' => [true, true, true];

		yield 'class-or-trait' => [true, true, false];

		yield 'class-or-interface' => [true, false, true];

		yield 'trait-or-interface' => [false, true, true];

		yield 'class-only' => [true, false, false];

		yield 'trait-only' => [false, true, false];

		yield 'interface-only' => [false, false, true];
	}
}
