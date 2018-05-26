<?php namespace Tests\Support\Config;

use Config\Autoload;

class MockAutoload extends Autoload
{
	public $psr4 = [];

	public $classmap = [];

	//--------------------------------------------------------------------

	public function __construct()
	{
		// Don't call the parent since we don't want the default mappings.
		// parent::__construct();
	}

	//--------------------------------------------------------------------

}
