<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Debug\Deprecation;

use BadMethodCallException;
use Error;
use ReflectionMethod;

/**
 * This support trait can be used in two flavors.
 * - Used to prevent child classes from using deprecated parent methods;
 * - Used to prevent method calls outside the class.
 *
 * __(1) Used to prevent child classes from using deprecated parent methods__
 *
 * If intention is to encapsulate code within the parent class, this trait
 * should be imported into the parent. The deprecated method should be set
 * as `private` and the name and its replacement will be setup in a private
 * `$deprecatedMethods` property array. The property is set private to prevent
 * overrides by child classes. If the deprecated method is static, the setup
 * will be made in a private static `$deprecatedStaticMethods` property array.
 *
 * __(2) Used to prevent method calls outside the class__
 *
 * To prevent out-of-class method calls of the deprecated methods, the method
 * should be set as either `protected` or `private` (i.e., preventing access).
 * After that, add the name of the method and its replacement into the respective
 * array property, depending if the method is static or not.
 *
 * __NOTE:__
 *
 * This trait will give access to previously inaccessible methods of a class. If this
 * behavior is not desired, the names of methods to be excluded and to remain as
 * not accessible should be added to the private static `$methodAccessExclusions`
 * property.
 *
 * @property-read array<string, string>  $deprecatedMethods
 * @property-read array<string, string>  $deprecatedStaticMethods
 * @property-read array<integer, string> $methodAccessExclusions
 */
trait DeprecatedClassMethodTrait
{
	/**
	 * Intercept calls to inaccessible and/or deprecated non-static methods.
	 *
	 * @param string  $name      Name of the static method
	 * @param mixed[] $arguments Arguments to be passed
	 *
	 * @throws Error                  When the method is not deprecated but is not allowed for access
	 * @throws BadMethodCallException When the method is not defined
	 * @throws DeprecationException   When the method is deprecated and handling mode is `Deprecation::THROW_EXCEPTION`
	 *
	 * @return mixed
	 */
	public function __call(string $name, array $arguments)
	{
		if (! method_exists($this, $name))
		{
			throw new BadMethodCallException(sprintf('Trying to access undefined method "%s::%s()".', static::class, $name));
		}

		if (array_key_exists($name, $this->deprecatedMethods))
		{
			// a deprecated method is always resolved to the class importing
			// this trait, so this is always `self::class`
			$classMethod = self::class . '::' . $name;

			Deprecation::triggerForMethod($classMethod, $this->deprecatedMethods[$name]);
		}

		// did we accidentally grant access to methods we should not let everyone to use?
		if (in_array($name, self::$methodAccessExclusions, true))
		{
			$visibility = self::determineMethodVisibility($name);
			$backtrace  = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

			throw new Error(sprintf(
				'Call to %s method "%s::%s()" from scope "%s".',
				$visibility,
				self::class,
				$name,
				$backtrace[1]['class'] ?? $backtrace[2]['class'] ?? 'unknown'
			));
		}

		return $this->{$name}(...$arguments);
	}

	/**
	 * Intercept calls to inaccessible and/or deprecated static methods.
	 *
	 * @param string  $name      Name of the static method
	 * @param mixed[] $arguments Arguments to be passed
	 *
	 * @throws Error                  When the method is not deprecated but is not allowed for access
	 * @throws BadMethodCallException When the method is not defined
	 * @throws DeprecationException   When the method is deprecated and handling mode is `Deprecation::THROW_EXCEPTION`
	 *
	 * @return mixed
	 */
	public static function __callStatic(string $name, array $arguments)
	{
		if (! method_exists(self::class, $name))
		{
			throw new BadMethodCallException(sprintf('Trying to access undefined static method "%s::%s()".', self::class, $name));
		}

		if (array_key_exists($name, self::$deprecatedStaticMethods))
		{
			// a deprecated static method is always resolved to the class
			// importing this trait, so this is always `self::class`
			$classMethod = self::class . '::' . $name;

			Deprecation::triggerForStaticMethod($classMethod, self::$deprecatedStaticMethods[$name]);
		}

		// did we accidentally grant access to methods we should not let everyone to use?
		if (in_array($name, self::$methodAccessExclusions, true))
		{
			$visibility = self::determineMethodVisibility($name);
			$backtrace  = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

			throw new Error(sprintf(
				'Call to %s static method "%s::%s()" from scope "%s".',
				$visibility,
				self::class,
				$name,
				$backtrace[1]['class'] ?? $backtrace[2]['class'] ?? 'unknown'
			));
		}

		return static::{$name}(...$arguments);
	}

	private static function determineMethodVisibility(string $method): string
	{
		$method = new ReflectionMethod(self::class, $method);

		if ($method->isPrivate())
		{
			return 'private';
		}

		if ($method->isProtected())
		{
			return 'protected';
		}

		return 'public'; // @codeCoverageIgnore
	}
}
