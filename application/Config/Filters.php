<?php namespace Config;

class Filters
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
	public $global = [
		'isLoggedIn'               => ['except' => 'login'],
		'CodeIgniter\Filters\CSRF' => '*'
	];

	public $routes = [
		'admin/*' => [
			'before' => ['isLoggedIn'],
			'after' => ['somethingElse']
		]
	];
}

public $filters = [

	'isLoggedIn'    => ['admin/*', 'profiles/*', 'users/*', 'posts/*/store', 'posts/*/destroy'],
	'somethignElse' => ['admin/*', 'profiles/*', 'users/*', 'posts/*/store', 'posts/*/destroy'],
];