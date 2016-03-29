<?php namespace CodeIgniter;

class MockBootstrap extends Bootstrap
{
	protected function setupAutoloader()
	{
		$loader = parent::setupAutoloader();

		// Add namespace paths to autoload mocks for testing
		$loader->addNamespace('CodeIgniter', SUPPORTPATH);
		$loader->addNamespace('Config', SUPPORTPATH.'Config');
	}

	//--------------------------------------------------------------------
}
