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

use Psr\Log\LogLevel;
use UnexpectedValueException;

/**
 * The Deprecation class manages usage of deprecated elements in the framework.
 */
final class Deprecation
{
	/**
	 * Mode to log the deprecation messages instead of halting the application.
	 *
	 * @var string
	 */
	public const LOG_MESSAGE = 'log_message';

	/**
	 * Mode to throw a `DeprecationException` for all encountered uses
	 * of deprecated elements.
	 *
	 * @var string
	 */
	public const THROW_EXCEPTION = 'throw_exception';

	/**
	 * List of supported deprecation handling modes.
	 *
	 * @var array<integer, string>
	 */
	public const SUPPORTED_MODES = [self::LOG_MESSAGE, self::THROW_EXCEPTION];

	/**
	 * Current deprecation handling mode.
	 *
	 * @var string
	 */
	private static $mode = self::LOG_MESSAGE;

	/**
	 * @codeCoverageIgnore
	 */
	private function __construct()
	{
	}

	/**
	 * Set the deprecation handling mode. Allowed values can be any
	 * of `Deprecation::THROW_EXCEPTION` or `Deprecation::LOG_MESSAGE`.
	 *
	 * Default mode is `Deprecation::LOG_MESSAGE`.
	 *
	 * @param string $mode Any of `Deprecation::THROW_EXCEPTION` or `Deprecation::LOG_MESSAGE`
	 *
	 * @throws UnexpectedValueException When mode is not supported
	 *
	 * @return void
	 */
	public static function setMode(string $mode): void
	{
		if (! in_array($mode, self::SUPPORTED_MODES, true))
		{
			throw new UnexpectedValueException(sprintf(
				'Mode "%s" is not supported. Allowed: "%s".',
				$mode,
				implode('", "', self::SUPPORTED_MODES)
			));
		}

		self::$mode = $mode;
	}

	/**
	 * Retrieve the current deprecation handling mode.
	 *
	 * @return string
	 */
	public static function mode(): string
	{
		return self::$mode;
	}

	/**
	 * Generic trigger of deprecation message.
	 *
	 * @param string $message Deprecation message
	 * @param string $level   Log level for logging the deprecation message, default is `error`
	 *
	 * @throws DeprecationException
	 *
	 * @return void
	 */
	public static function trigger(string $message, string $level = LogLevel::ERROR): void
	{
		self::triggerDeprecation($message, $level);
	}

	/**
	 * Trigger a deprecation message when a deprecated class is used.
	 *
	 * @param string $deprecated  Fully qualified name of deprecated class
	 * @param string $replacement Fully qualified name of replacement class
	 * @param string $level       Log level for logging the deprecation message, default is `error`
	 *
	 * @throws DeprecationException
	 * @throws UnexpectedValueException
	 *
	 * @return void
	 */
	public static function triggerForClass(string $deprecated, string $replacement, string $level = LogLevel::ERROR): void
	{
		self::determineIfValidObjectType('deprecated', $deprecated, true, false, false);
		self::determineIfValidObjectType('replacement', $replacement, true, true, false);

		$message = lang('Deprecation.classDeprecated', [$deprecated, $replacement]);

		self::triggerDeprecation($message, $level);
	}

	/**
	 * Trigger a deprecation message on use of deprecated interface.
	 *
	 * @param string $deprecated  Fully qualified name of deprecated interface
	 * @param string $replacement Fully qualified name of replacement class or interface
	 * @param string $level       Log level for logging the deprecation message, default is `error`
	 *
	 * @throws DeprecationException
	 * @throws UnexpectedValueException
	 *
	 * @return void
	 */
	public static function triggerForInterface(string $deprecated, string $replacement, string $level = LogLevel::ERROR): void
	{
		self::determineIfValidObjectType('deprecated', $deprecated, false, false, true);
		self::determineIfValidObjectType('replacement', $replacement, true, false, true);

		$message = lang('Deprecation.interfaceDeprecated', [$deprecated, $replacement]);

		self::triggerDeprecation($message, $level);
	}

	/**
	 * Trigger a deprecation message when a deprecated trait is used.
	 *
	 * @param string $deprecated  Fully qualified name of deprecated trait
	 * @param string $replacement Fully qualified name of replacement class or trait
	 * @param string $level       Log level for logging the deprecation message, default is `error`
	 *
	 * @throws DeprecationException
	 * @throws UnexpectedValueException
	 *
	 * @return void
	 */
	public static function triggerForTrait(string $deprecated, string $replacement, string $level = LogLevel::ERROR): void
	{
		self::determineIfValidObjectType('deprecated', $deprecated, false, true, false);
		self::determineIfValidObjectType('replacement', $replacement, true, true, false);

		$message = lang('Deprecation.traitDeprecated', [$deprecated, $replacement]);

		self::triggerDeprecation($message, $level);
	}

	/**
	 * Trigger a deprecation message for retrieving the value of a deprecated class property.
	 *
	 * @param string $property    Name of the property
	 * @param string $class       Name of the class where the property belongs
	 * @param string $replacement Name of replacing property
	 * @param string $level       Log level for logging the deprecation message, default is `error`
	 *
	 * @throws DeprecationException
	 *
	 * @return void
	 */
	public static function triggerForPropertyAccess(string $property, string $class, string $replacement, string $level = LogLevel::ERROR): void
	{
		self::determineIfValidObjectType('class', $class, true, false, false);
		$property = self::normalizeVariablesWithDollars($property);
		$message  = lang('Deprecation.propertyAccessDeprecated', [$property, $class, $replacement]);

		self::triggerDeprecation($message, $level);
	}

	/**
	 * Trigger a deprecation message when trying to assign a value to a deprecated class property.
	 *
	 * @param string $property Name of the property
	 * @param string $class    Name of the class where the property belongs
	 * @param string $level    Log level for logging the deprecation message, default is `error`
	 *
	 * @throws DeprecationException
	 *
	 * @return void
	 */
	public static function triggerForPropertyAssignment(string $property, string $class, string $level = LogLevel::ERROR): void
	{
		self::determineIfValidObjectType('class', $class, true, false, false);
		$property = self::normalizeVariablesWithDollars($property);
		$message  = lang('Deprecation.propertyAssignmentDeprecated', [$property, $class]);

		self::triggerDeprecation($message, $level);
	}

	/**
	 * Trigger a deprecation for use of a non-static class method already marked as deprecated.
	 *
	 * @param string $method      Complete name of the deprecated method, usually given as `__METHOD__`
	 * @param string $replacement Complete name of the replacement method
	 * @param string $level       Log level for logging the deprecation message, default is `error`
	 *
	 * @throws DeprecationException
	 *
	 * @return void
	 */
	public static function triggerForMethod(string $method, string $replacement, string $level = LogLevel::ERROR): void
	{
		$classMethod = self::normalizeMethodsWithParens($method);
		$replacement = self::normalizeMethodsWithParens($replacement);

		$message = lang('Deprecation.methodAccessDeprecated', [$classMethod, $replacement]);

		self::triggerDeprecation($message, $level);
	}

	/**
	 * Trigger a deprecation for use of a static class method already marked as deprecated.
	 *
	 * @param string $staticMethod Complete name of the deprecated static method
	 * @param string $replacement  Complete name of the replacement method
	 * @param string $level        Log level for logging the deprecation message, default is `error`
	 *
	 * @throws DeprecationException
	 *
	 * @return void
	 */
	public static function triggerForStaticMethod(string $staticMethod, string $replacement, string $level = LogLevel::ERROR): void
	{
		$staticMethod = self::normalizeMethodsWithParens($staticMethod);
		$replacement  = self::normalizeMethodsWithParens($replacement);

		$message = lang('Deprecation.methodStaticAccessDeprecated', [$staticMethod, $replacement]);

		self::triggerDeprecation($message, $level);
	}

	/**
	 * Trigger deprecation message for a set of deprecated parameters of a class method.
	 *
	 * @param string|string[] $parameterNames Name(s) of the parameter(s)
	 * @param string          $classMethod    Method where the parameter is used
	 * @param string          $level          Log level for logging the deprecation message, default is `error`
	 *
	 * @throws DeprecationException
	 *
	 * @return void
	 */
	public static function triggerForMethodParameter($parameterNames, string $classMethod, string $level = LogLevel::ERROR): void
	{
		$parameterNames = array_map(static function (string $parameterName): string {
			return self::normalizeVariablesWithDollars($parameterName);
		}, (array) $parameterNames);

		$paramCount  = count($parameterNames);
		$parameters  = implode('", "', $parameterNames);
		$classMethod = self::normalizeMethodsWithParens($classMethod);

		$message = $paramCount > 1
			? lang('Deprecation.methodParametersDeprecated', [$parameters, $classMethod])
			: lang('Deprecation.methodParameterDeprecated', [$parameters, $classMethod]);

		self::triggerDeprecation($message, $level);
	}

	/**
	 * Utility method to detect uses of deprecated interfaces.
	 *
	 * __NOTE:__
	 * Deprecation warning cannot be setup after the interface definition because
	 * PHP only resolves the interface once.
	 *
	 * @param string|object $objectOrClass A class instance or class name
	 * @param string        $deprecated    Name of deprecated interface
	 * @param string        $replacement   Name of replacement interface or class
	 * @param string        $level         Log level for logging the deprecation message, default is `error`
	 *
	 * @throws DeprecationException
	 * @throws UnexpectedValueException
	 *
	 * @return void
	 */
	public static function checkDeprecatedInterface($objectOrClass, string $deprecated, string $replacement, string $level = LogLevel::ERROR): void
	{
		if (in_array($deprecated, class_implements($objectOrClass), true))
		{
			self::triggerForInterface($deprecated, $replacement, $level);
		}
	}

	/**
	 * Utility method to detect uses of deprecated traits.
	 *
	 * __NOTE:__
	 * Deprecation warning cannot be setup after the trait definition because
	 * PHP will emit a fatal error during class fetch.
	 *
	 * @param string|object $objectOrClass A class instance or class name
	 * @param string        $deprecated    Name of deprecated trait
	 * @param string        $replacement   Name of replacement trait or class
	 * @param string        $level         Log level for logging the deprecation message, default is `error`
	 *
	 * @throws DeprecationException
	 * @throws UnexpectedValueException
	 *
	 * @return void
	 */
	public static function checkDeprecatedTrait($objectOrClass, string $deprecated, string $replacement, string $level = LogLevel::ERROR): void
	{
		if (in_array($deprecated, class_uses($objectOrClass), true))
		{
			self::triggerForTrait($deprecated, $replacement, $level);
		}
	}

	/**
	 * Internal method to trigger the deprecation message based on
	 * the selected handling mode.
	 *
	 * @param string $message
	 * @param string $level
	 *
	 * @throws DeprecationException
	 *
	 * @return void
	 */
	private static function triggerDeprecation(string $message, string $level): void
	{
		if (self::$mode === self::THROW_EXCEPTION)
		{
			throw new DeprecationException($message);
		}

		// Always make sure the message is prepended
		if (substr($message, 0, 12) !== 'DEPRECATED: ')
		{
			$message = 'DEPRECATED: ' . $message;
		}

		log_message($level, $message);
	}

	/**
	 * Internal method to normalize names of properties and parameters to always
	 * start with a dollar sign (`$`).
	 *
	 * @param string $variable
	 *
	 * @return string
	 */
	private static function normalizeVariablesWithDollars(string $variable): string
	{
		if (substr($variable, 0, 1) !== '$')
		{
			$variable = '$' . $variable;
		}

		return $variable;
	}

	/**
	 * Internal function to normalize names of methods with
	 * a pair of parenthesis at their ends.
	 *
	 * @param string $method
	 *
	 * @return string
	 */
	private static function normalizeMethodsWithParens(string $method): string
	{
		if (substr($method, -1, 2) !== '()')
		{
			$method .= '()';
		}

		return $method;
	}

	/**
	 * Internal method to determine if a valid object type is given,
	 * and throw an `UnexpectedValueException` if not.
	 *
	 * @param string  $parameter
	 * @param string  $argument
	 * @param boolean $classType
	 * @param boolean $traitType
	 * @param boolean $interfaceType
	 *
	 * @throws UnexpectedValueException
	 *
	 * @return void
	 */
	private static function determineIfValidObjectType(string $parameter, string $argument, bool $classType, bool $traitType, bool $interfaceType): void
	{
		$allObjectTypes   = $classType && $traitType && $interfaceType;
		$classOrTrait     = $classType && $traitType;
		$classOrInterface = $classType && $interfaceType;
		$traitOrInterface = $traitType && $interfaceType;

		$parameter = self::normalizeVariablesWithDollars($parameter);

		// this check is very highly unlikely to be imposed but added here for prudence
		if ($allObjectTypes && ! (class_exists($argument) || trait_exists($argument) || interface_exists($argument)))
		{
			throw new UnexpectedValueException(sprintf('Argument "%s" passed to parameter "%s" is neither a valid class, trait nor interface.', $argument, $parameter));
		}

		// disjunctive type: class OR trait
		if ($classOrTrait && ! (class_exists($argument) || trait_exists($argument)))
		{
			throw new UnexpectedValueException(sprintf('Argument "%s" passed to parameter "%s" is neither a valid class nor trait.', $argument, $parameter));
		}

		// disjunctive type: class OR interface
		if ($classOrInterface && ! (class_exists($argument) || interface_exists($argument)))
		{
			throw new UnexpectedValueException(sprintf('Argument "%s" passed to parameter "%s" is neither a valid class nor interface.', $argument, $parameter));
		}

		// disjunctive type: trait OR interface
		if ($traitOrInterface && ! (trait_exists($argument) || interface_exists($argument)))
		{
			throw new UnexpectedValueException(sprintf('Argument "%s" passed to parameter "%s" is neither a valid trait nor interface.', $argument, $parameter));
		}

		// not a disjunctive type, so check if class
		if (! ($classOrTrait || $classOrInterface) && $classType && ! class_exists($argument))
		{
			throw new UnexpectedValueException(sprintf('Argument "%s" passed to parameter "%s" is not a valid class.', $argument, $parameter));
		}

		// not a disjunctive type, so check if trait
		if (! ($classOrTrait || $traitOrInterface) && $traitType && ! trait_exists($argument))
		{
			throw new UnexpectedValueException(sprintf('Argument "%s" passed to parameter "%s" is not a valid trait.', $argument, $parameter));
		}

		// not a disjunctive type, so check if interface
		if (! ($classOrInterface || $traitOrInterface) && $interfaceType && ! interface_exists($argument))
		{
			throw new UnexpectedValueException(sprintf('Argument "%s" passed to parameter "%s" is not a valid interface.', $argument, $parameter));
		}
	}
}
