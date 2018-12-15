<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class Toolbar extends BaseConfig
{
	/*
	|--------------------------------------------------------------------------
	| Debug Toolbar
	|--------------------------------------------------------------------------
	| The Debug Toolbar provides a way to see information about the performance
	| and state of your application during that page display. By default it will
	| NOT be displayed under production environments, and will only display if
	| CI_DEBUG is true, since if it's not, there's not much to display anyway.
	|
	| toolbarMaxHistory = Number of history files, 0 for none or -1 for unlimited
	|
	*/
	public $collectors = [
		\CodeIgniter\Debug\Toolbar\Collectors\Timers::class,
		\CodeIgniter\Debug\Toolbar\Collectors\Database::class,
		\CodeIgniter\Debug\Toolbar\Collectors\Logs::class,
		\CodeIgniter\Debug\Toolbar\Collectors\Views::class,
		// \CodeIgniter\Debug\Toolbar\Collectors\Cache::class,
		\CodeIgniter\Debug\Toolbar\Collectors\Files::class,
		\CodeIgniter\Debug\Toolbar\Collectors\Routes::class,
		\CodeIgniter\Debug\Toolbar\Collectors\Events::class,
	];
	public $maxHistory = 20;
	public $viewsPath  = SYSTEMPATH . 'Debug/Toolbar/Views/';
}
