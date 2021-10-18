<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

/**
 * Class FeatureTestCase
 *
 * Provides a base class with the trait for doing full HTTP testing
 * against your application.
 *
 * @no-final
 *
 * @deprecated Use FeatureTestTrait instead
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
class FeatureTestCase extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
}
