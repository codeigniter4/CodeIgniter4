<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Mock;

use \CodeIgniter\Config\BaseService;
use CodeIgniter\Autoloader\FileLocator;

class MockServices extends BaseService
{
	public $psr4 = [
		'Tests/Support' => TESTPATH . '_support/',
	];

	public $classmap = [];

	//--------------------------------------------------------------------

	public function __construct()
	{
		// Don't call the parent since we don't want the default mappings.
		// parent::__construct();
	}

	//--------------------------------------------------------------------
	public static function locator(bool $getShared = true)
	{
		return new FileLocator(static::autoloader());
	}
}
