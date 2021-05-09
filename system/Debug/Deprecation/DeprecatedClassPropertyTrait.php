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

use Error;
use LogicException;
use ReflectionProperty;

/**
 * Support trait in order to properly intercept calls to deprecated class
 * properties. This should be imported to classes where the properties are
 * defined. The deprecated properties should no longer be accessible
 * outside the class by setting its visibility either as `protected` or `private`.
 * If used in the context of forbidding child classes to use the properties,
 * the visibility should be set as `private` instead.
 *
 * Two special array properties are needed for this to work:
 * - `$deprecatedProperties` - Array containing the name of the deprecated
 * 		property as the key and the suggested replacement as value. This is
 *      used when a value of a deprecated property is retrieved.
 * - `$deprecatedSettableProperties` - Array containing the name of the
 *      deprecated property which is forbidden to be given a value. This is
 * 		a simple list consisting of the names as array values.
 *
 * __NOTE:__
 *
 * This trait will give access to previously inaccessible properties. If a global
 * access is not intended and intercept should be done only on the deprecated
 * properties, then the names to be excluded should be added in the private
 * static `$propertyAccessExclusions` property.
 *
 * @property-read array<string, string>  $deprecatedProperties
 * @property-read array<integer, string> $deprecatedSettableProperties
 * @property-read array<integer, string> $propertyAccessExclusions
 */
trait DeprecatedClassPropertyTrait
{
	/**
	 * Intercept property access to inaccessible and/or deprecated properties.
	 *
	 * @param string $property Name of property to inspect
	 *
	 * @throws Error                When the property is not deprecated but is not allowed for access
	 * @throws LogicException       When property is not defined by the class
	 * @throws DeprecationException When property is deprecated and handling mode is `Deprecation::THROW_EXCEPTION`
	 *
	 * @return mixed
	 */
	public function __get(string $property)
	{
		if (! property_exists($this, $property))
		{
			throw new LogicException(sprintf('Trying to access unknown property "%s::$%s".', static::class, $property));
		}

		if (array_key_exists($property, $this->deprecatedProperties))
		{
			Deprecation::triggerForPropertyAccess($property, static::class, $this->deprecatedProperties[$property]);
		}

		// did we accidentally grant access to inaccessible properties?
		if (in_array($property, self::$propertyAccessExclusions, true))
		{
			$visibility = self::determinePropertyVisibility($property);
			$backtrace  = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

			throw new Error(sprintf(
				'Cannot access %s property "%s::$%s" from scope "%s".',
				$visibility,
				self::class,
				$property,
				$backtrace[1]['class'] ?? $backtrace[2]['class'] ?? 'unknown'
			));
		}

		return $this->$property;
	}

	/**
	 * Set a value to a property. If the property is deprecated and
	 * setting values to it is disallowed, this will issue a deprecation
	 * message.
	 *
	 * @param string $property Name of property to inspect
	 * @param mixed  $value    Value to assign
	 *
	 * @throws Error                When the property is not deprecated but is not allowed for assignment
	 * @throws LogicException       When the property is not defined in the class
	 * @throws DeprecationException When the property is deprecated and handling mode is `Deprecation::THROW_EXCEPTION`
	 *
	 * @return void
	 */
	public function __set(string $property, $value): void
	{
		if (! property_exists($this, $property))
		{
			throw new LogicException(sprintf('Trying to set value on unknown property "%s::$%s"', static::class, $property));
		}

		if (in_array($property, $this->deprecatedSettableProperties, true))
		{
			Deprecation::triggerForPropertyAssignment($property, static::class);
		}

		// did we accidentally grant access to inaccessible properties?
		if (in_array($property, self::$propertyAccessExclusions, true))
		{
			$visibility = self::determinePropertyVisibility($property);
			$backtrace  = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

			throw new Error(sprintf(
				'Cannot assign value to %s property "%s::$%s" from scope "%s".',
				$visibility,
				self::class,
				$property,
				$backtrace[1]['class'] ?? $backtrace[2]['class'] ?? 'unknown'
			));
		}

		$this->$property = $value;
	}

	private static function determinePropertyVisibility(string $property): string
	{
		$property = new ReflectionProperty(self::class, $property);

		if ($property->isPrivate())
		{
			return 'private';
		}

		if ($property->isProtected())
		{
			return 'protected';
		}

		return 'public'; // @codeCoverageIgnore
	}
}
