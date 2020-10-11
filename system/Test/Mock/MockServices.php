<?php namespace CodeIgniter\Test\Mock;

use \CodeIgniter\Config\BaseService;
use CodeIgniter\Autoloader\FileLocator;

class MockServices extends BaseService
{

	public $psr4     = [
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
