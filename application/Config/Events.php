<?php namespace Config;

use CodeIgniter\Events\Events;

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */



/*
 * --------------------------------------------------------------------
 * Debug Toolbar Listeners.
 * --------------------------------------------------------------------
 * If you delete, they will no longer be collected.
 */
if (ENVIRONMENT != 'production')
{
	Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');

	// Handles the display of the toolbar itself. MUST remain here for toolbar to be displayed.
	Events::on('pre_system', 'CodeIgniter\Debug\Toolbar::eventHandler');
}
