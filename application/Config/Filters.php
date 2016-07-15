<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
	// Makes reading things below nicer,
	// and simpler to change out script that's used.
	public $aliases = [
		'isLoggedIn' => 'App\Filters\Authentication',
		'apiPrep' => [
			'App\Filters\First',
			'App\Filters\Second',
		]
	];

	// Always applied before every request
	public $globals = [
		'before' => [
			'isLoggedIn' => ['except' => 'login'],
			'CodeIgniter\Filters\CSRF',
			'FullPageCache'
		],
		'after' => [
			'FullPageCache'            => '*'
		]
	];

	// Works on all of a particular HTTP method
	// (GET, POST, etc) as BEFORE filters only
	public $methods = [
		'post' => ['CSRF', 'throttle'],
		'ajax' => ['restrictToAJAX'],
		'cli'  => ['restrictToCLI']
	];

	public $filters = [
		'isLoggedIn' => ['before' => ['account/*', 'profiles/*']],
		'adminAuth'  => ['before' => ['admin/*']],
		'apiPrep'    => ['before' => ['api/*']],
	];
}
