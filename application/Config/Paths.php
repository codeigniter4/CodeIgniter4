<?php namespace Config;

/**
 * Holds the paths that are used by the system to
 * locate the main directories, application, system, etc.
 * Modifying these allows you to re-structure your application,
 * share a system folder between multiple applications, and more.
 *
 * All paths are relative to the project's root folder.
 */
class Paths
{
	/*
	 *---------------------------------------------------------------
	 * SYSTEM FOLDER NAME
	 *---------------------------------------------------------------
	 *
	 * This variable must contain the name of your "system" folder.
	 * Include the path if the folder is not in the same directory
	 * as this file.
	 */
	public $systemDirectory = 'system';

	/*
	 *---------------------------------------------------------------
	 * APPLICATION FOLDER NAME
	 *---------------------------------------------------------------
	 *
	 * If you want this front controller to use a different "application"
	 * folder than the default one you can set its name here. The folder
	 * can also be renamed or relocated anywhere on your getServer. If
	 * you do, use a full getServer path. For more info please see the user guide:
	 * http://codeigniter.com/user_guide/general/managing_apps.html
	 *
	 * NO TRAILING SLASH!
	 */
	public $applicationDirectory = 'application';

	/*
	 * ---------------------------------------------------------------
	 * WRITABLE DIRECTORY NAME
	 * ---------------------------------------------------------------
	 *
	 * This variable must contain the name of your "writable" directory.
	 * The writable directory allows you to group all directories that
	 * need write permission to a single place that can be tucked away
	 * for maximum security, keeping it out of the application and/or
	 * system directories.
	 */
	public $writableDirectory = 'writable';

	/*
	 * ---------------------------------------------------------------
	 * TESTS DIRECTORY NAME
	 * ---------------------------------------------------------------
	 *
	 * This variable must contain the name of your "tests" directory.
	 * The writable directory allows you to group all directories that
	 * need write permission to a single place that can be tucked away
	 * for maximum security, keeping it out of the application and/or
	 * system directories.
	 */
	public $testsDirectory = 'tests';

	/*
	 * ---------------------------------------------------------------
	 * PUBLIC DIRECTORY NAME
	 * ---------------------------------------------------------------
	 *
	 * This variable must contain the name of the directory that
	 * contains the main index.php front-controller. By default,
	 * this is the `public` directory, but some hosts may not
	 * be able to map a primary domain to a sub-directory so you
	 * can change this to `public_html`, for example, to comply
	 * with your host's needs.
	 */
	public $publicDirectory = 'public';

	/*
	 * ---------------------------------------------------------------
	 * VIEW DIRECTORY NAME
	 * ---------------------------------------------------------------
	 *
	 * This variable must contain the name of the directory that
	 * contains the view files used by your application. By
	 * default this is in `application/Views`. This value
	 * is used when no value is provided to `Services::renderer()`.
	 */
	public $viewDirectory = 'application/Views';
}
