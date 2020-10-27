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

use CodeIgniter\Router\RouteCollection;

/**
 * Class FeatureTestCase
 *
 * Provides a base class with the trait for doing full HTTP testing
 * against your application.
 *
 * @package CodeIgniter\Test
 */
class FeatureTestCase extends CIDatabaseTestCase
{
	use FeatureTestTrait;

	/**
	 * If present, will override application
	 * routes when using call().
	 *
	 * @var RouteCollection
	 */
	protected $routes;

	/**
	 * Values to be set in the SESSION global
	 * before running the test.
	 *
	 * @var array
	 */
	protected $session = [];

	/**
	 * Enabled auto clean op buffer after request call
	 *
	 * @var boolean
	 */
	protected $clean = true;

	/**
	 * Custom request's headers
	 *
	 * @var array
	 */
	protected $headers = [];
}
