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

	protected function getServicesFactory()
	{
		// Use Services class for testing
		require_once SUPPORTPATH.'Config/Services.php';
	}
}
