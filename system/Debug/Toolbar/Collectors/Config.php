<?php namespace CodeIgniter\Debug\Toolbar\Collectors;

use CodeIgniter\CodeIgniter;
use Config\App;
use Config\Services;

class Config
{
	public static function display()
	{
		$config = new App();

		return [
			'ciVersion'   => CodeIgniter::CI_VERSION,
			'phpVersion'  => phpversion(),
			'phpSAPI'     => php_sapi_name(),
			'environment' => ENVIRONMENT,
			'baseURL'     => $config->baseURL,
			'timezone'    => app_timezone(),
			'locale'      => Services::request()->getLocale(),
			'cspEnabled'  => $config->CSPEnabled,
			'salt'        => $config->salt,
		];
	}
}
