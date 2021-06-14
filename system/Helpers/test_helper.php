<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use CodeIgniter\Exceptions\TestException;
use CodeIgniter\Test\Fabricator;
use Config\Services;

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
	function fake($model, array $overrides = null, $persist = true)
	{
		// Get a model-appropriate Fabricator instance
		$fabricator = new Fabricator($model);

		// Set overriding data, if necessary
		if ($overrides)
		{
			$fabricator->setOverrides($overrides);
		}

		if ($persist)
		{
			return $fabricator->create();
		}

		return $fabricator->make();
	}
}

if (! function_exists('mock'))
{
	/**
	 * Used within our test suite to mock certain system tools.
	 *
	 * @param string $className Fully qualified class name
	 */
	function mock(string $className)
	{
		$mockClass   = $className::$mockClass;
		$mockService = $className::$mockServiceName;

		if (empty($mockClass) || ! class_exists($mockClass))
		{
			throw TestException::forInvalidMockClass($mockClass);
		}

		$mock = new $mockClass();

		if (! empty($mockService))
		{
			Services::injectMock($mockService, $mock);
		}

		return $mock;
	}
}
