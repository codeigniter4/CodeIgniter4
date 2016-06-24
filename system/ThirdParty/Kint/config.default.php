<?php
isset( $GLOBALS['_kint_settings'] ) or $GLOBALS['_kint_settings'] = array();
$_kintSettings = &$GLOBALS['_kint_settings'];


/** @var bool if set to false, kint will become silent, same as Kint::enabled(false) or Kint::$enabled = false */
$_kintSettings['enabled'] = true;


/**
 * @var bool whether to display where kint was called from
 */
$_kintSettings['displayCalledFrom'] = true;


/**
 * @var string format of the link to the source file in trace entries. Use %f for file path, %l for line number.
 * Defaults to xdebug.file_link_format if not set.
 *
 * [!] EXAMPLE (works with for phpStorm and RemoteCall Plugin):
 *
 * $_kintSettings['fileLinkFormat'] = 'http://localhost:8091/?message=%f:%l';
 *
 */
$_kintSettings['fileLinkFormat'] = ini_get( 'xdebug.file_link_format' );


/**
 * @var array base directories of your application that will be displayed instead of the full path. Keys are paths,
 * values are replacement strings
 *
 * Defaults to array( $_SERVER['DOCUMENT_ROOT'] => '&lt;ROOT&gt;' )
 *
 * [!] EXAMPLE (for Kohana framework):
 *
 * $_kintSettings['appRootDirs'] = array(
 *      APPPATH => 'APPPATH', // make sure the constants are already defined at the time of including this config file
 *      SYSPATH => 'SYSPATH',
 *      MODPATH => 'MODPATH',
 *      DOCROOT => 'DOCROOT',
 * );
 *
 * [!] EXAMPLE #2 (for a semi-universal approach)
 *
 * $_kintSettings['appRootDirs'] = array(
 *      realpath( __DIR__ . '/../../..' ) => 'ROOT', // go up as many levels as needed in the realpath() param
 * );
 *
 * $_kintSettings['fileLinkFormat'] = 'http://localhost:8091/?message=%f:%l';
 *
 */
$_kintSettings['appRootDirs'] = isset( $_SERVER['DOCUMENT_ROOT'] )
	? array( $_SERVER['DOCUMENT_ROOT'] => '&lt;ROOT&gt;' )
	: array();


/** @var int max length of string before it is truncated and displayed separately in full. Zero or false to disable */
$_kintSettings['maxStrLength'] = 80;

/** @var array possible alternative char encodings in order of probability, eg. array('windows-1251') */
$_kintSettings['charEncodings'] = array(
	'UTF-8',
	'Windows-1252', # Western; includes iso-8859-1, replace this with windows-1251 if you have Russian code
	'euc-jp',       # Japanese

	# all other charsets cannot be differentiated by PHP and/or are not supported by mb_* functions,
	# I need a better means of detecting the codeset, no idea how though :(

	//		'iso-8859-13',  # Baltic
	//		'windows-1251', # Cyrillic
	//		'windows-1250', # Central European
	//		'shift_jis',    # Japanese
	//		'iso-2022-jp',  # Japanese
);


/** @var int max array/object levels to go deep, if zero no limits are applied */
$_kintSettings['maxLevels'] = 7;


/** @var string name of theme for rich view */
$_kintSettings['theme'] = 'original';


/** @var bool enable detection when Kint is command line. Formats output with whitespace only; does not HTML-escape it */
$_kintSettings['cliDetection'] = true;

/** @var bool in addition to above setting, enable detection when Kint is run in *UNIX* command line.
 * Attempts to add coloring, but if seen as plain text, the color information is visible as gibberish
 */
$_kintSettings['cliColors'] = true;


unset( $_kintSettings );