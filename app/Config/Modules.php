<?php

namespace Config;

use CodeIgniter\Modules\Modules as CoreModules;

class Modules extends CoreModules
{
	/*
	 |--------------------------------------------------------------------------
	 | Auto-Discovery Enabled?
	 |--------------------------------------------------------------------------
	 |
	 | If true, then auto-discovery will happen across all elements listed in
	 | $activeExplorers below. If false, no auto-discovery will happen at all,
	 | giving a slight performance boost.
	 */
	public $enabled = true;

	/*
	 |--------------------------------------------------------------------------
	 | Auto-Discovery Within Composer Packages Enabled?
	 |--------------------------------------------------------------------------
	 |
	 | If true, then auto-discovery will happen across all namespaces loaded
	 | by Composer, as well as the namespaces configured locally.
	 */
	public $discoverInComposer = true;

	/*
	|--------------------------------------------------------------------------
	| Auto-discover Rules
	|--------------------------------------------------------------------------
	|
	| Aliases list of all discovery classes that will be active and used during
	| the current application request.
	| If it is not listed, only the base application elements will be used.
	*/
	public $aliases = [
		'events',
		'registrars',
		'routes',
		'services',
	];
}
