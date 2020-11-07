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
