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

	Events::on('pre_system', function()
	{
		$request = \Config\Services::request();

        if ($request->getGet('debugbar') !== null)
        {
        	ob_start();
	        include(BASEPATH . 'Debug/Toolbar/toolbarloader.js.php');
	        $output = ob_get_contents();
	        @ob_end_clean();
		    exit($output);
		}

		if ($request->getGet('debugbar_time'))
		{
			helper('security');

			$file = sanitize_filename('debugbar_' . $request->getGet('debugbar_time'));
		    $filename = WRITEPATH . sanitize_filename('debugbar_' . $request->getGet('debugbar_time'));

		    if (file_exists($filename))
		    {
		    	$contents = file_get_contents($filename);
		    	unlink($filename);
			    exit($contents);
		    }

			// File was not written or do not exists
		    exit('<script id="toolbar_js">console.log(\'CI DebugBar: File "WRITEPATH/' . $file . '" not found.\')</script>');
		}
	});
}
