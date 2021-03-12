<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use CodeIgniter\Test\Fabricator;

/**
 * CodeIgniter Test Helpers
 */
//--------------------------------------------------------------------

if (! function_exists('fake'))
{
	/**
	 * Creates a single item using Fabricator.
	 *
	 * @param Model|object|string $model     Instance or name of the model
	 * @param array|null          $overrides Overriding data to pass to Fabricator::setOverrides()
	 *
	 * @return object|array
	 */
	function fake($model, array $overrides = null)
	{
		// Get a model-appropriate Fabricator instance
		$fabricator = new Fabricator($model);

		// Set overriding data, if necessary
		if ($overrides)
		{
			$fabricator->setOverrides($overrides);
		}

		return $fabricator->create();
	}
}

/**
 * These helpers come from Laravel so will not be
 * re-tested and can be ignored safely.
 *
 * @see https://github.com/laravel/framework/blob/8.x/src/Illuminate/Support/helpers.php
 */
// @codeCoverageIgnoreStart
if (! function_exists('class_basename'))
{
	/**
	 * Get the class "basename" of the given object / class.
	 *
	 * @param  string|object $class
	 * @return string
	 */
	function class_basename($class)
	{
		$class = is_object($class) ? get_class($class) : $class;

		return basename(str_replace('\\', '/', $class));
	}
}

if (! function_exists('class_uses_recursive'))
{
	/**
	 * Returns all traits used by a class, its parent classes and trait of their traits.
	 *
	 * @param  object|string $class
	 * @return array
	 */
	function class_uses_recursive($class)
	{
		if (is_object($class))
		{
			$class = get_class($class);
		}

		$results = [];

		// @phpstan-ignore-next-line
		foreach (array_reverse(class_parents($class)) + [$class => $class] as $class)
		{
			$results += trait_uses_recursive($class);
		}

		return array_unique($results);
	}
}

if (! function_exists('trait_uses_recursive'))
{
	/**
	 * Returns all traits used by a trait and its traits.
	 *
	 * @param  string $trait
	 * @return array
	 */
	function trait_uses_recursive($trait)
	{
		$traits = class_uses($trait) ?: [];

		foreach ($traits as $trait)
		{
			$traits += trait_uses_recursive($trait);
		}

		return $traits;
	}
}
// @codeCoverageIgnoreEnd
