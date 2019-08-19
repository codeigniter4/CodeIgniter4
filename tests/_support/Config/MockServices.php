<?php
namespace Tests\Support\Config;

use \CodeIgniter\Config\BaseService;

class MockServices extends BaseService
{

	public $psr4     = [
		'Tests/Support'                         => TESTPATH . '_support/',
		'Tests/Support/DatabaseTestMigrations'  => TESTPATH . '_support/DatabaseTestMigrations',
		'Tests/Support/MigrationTestMigrations' => TESTPATH . '_support/MigrationTestMigrations',
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
		return new \CodeIgniter\Autoloader\FileLocator(static::autoloader());
	}

}
