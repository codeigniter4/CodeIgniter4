<?php namespace CodeIgniter\Debug\Toolbar\Collectors;

use Config\App;
use Config\Services;
use CodeIgniter\CodeIgniter;

class Config
{
	public static function display()
	{
		$config = config(App::class);

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
