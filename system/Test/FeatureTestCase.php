<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

/**
 * Class FeatureTestCase
 *
 * Provides a base class with the trait for doing full HTTP testing
 * against your application.
 *
 * @deprecated Include the traits directly in the test case
 */
class FeatureTestCase extends CIUnitTestCase
{
	use FeatureTestTrait;
	use DatabaseTestTrait;
}
