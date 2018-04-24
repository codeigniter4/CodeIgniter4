<?php

/**
 * System messages translation for CodeIgniter(tm)
 * @author	    CodeIgniter community
 * @copyright	Copyright (c) 2014-2018, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	    http://opensource.org/licenses/MIT	MIT License
 * @link	    https://codeigniter.com
 */

return [
	// Migration Runner
	'migMissingTable'  	=> 'Migrations table must be set.',
	'migInvalidType'   	=> 'An invalid migration numbering type was specified: ',
	'migDisabled'      	=> 'Migrations have been loaded but are disabled or setup incorrectly.',
	'migNotFound'      	=> 'Migration file not found: ',
	'migEmpty'         	=> 'No Migration files found',
	'migGap'           	=> 'There is a gap in the migration sequence near version number: ',
	'migClassNotFound' 	=> 'The migration class "%s" could not be found.',
	'migMissingMethod' 	=> 'The migration class is missing an "%s" method.',
	'migMultiple'      	=> 'There are multiple migrations with the same version number: ',

	// Migration Command
	'migHelpLatest'    	=> "\t\tMigrates database to latest available migration.",
	'migHelpCurrent'   	=> "\t\tMigrates database to version set as 'current' in configuration.",
	'migHelpVersion'   	=> "\tMigrates database to version {v}.",
	'migHelpRollback'  	=> "\tRuns all migrations 'down' to version 0.",
	'migHelpRefresh'  	=> "\t\tUninstalls and re-runs all migrations to freshen database.",
	'migHelpSeed'      	=> "\tRuns the seeder named [name].",
	'migCreate'        	=> "\tCreates a new migration named [name]",
	'migNameMigration' 	=> 'Name the migration file',
	'migBadCreateName' 	=> 'You must provide a migration file name.',
	'migWriteError'    	=> 'Error trying to create file.',

	'migToLatest'       => 'Migrating to latest version...',
	'migInvalidVersion' => 'Invalid version number provided.',
	'migToVersionPH'    => 'Migrating to version %s...',
	'migToVersion'      => 'Migrating to current version...',
	'migRollingBack'    => 'Rolling back all migrations...',
	'migNoneFound'      => 'No migrations were found.',
	'migOn'             => 'Migrated On: ',
	'migSeeder'         => 'Seeder name',
	'migMissingSeeder'  => 'You must provide a seeder name.',
	'migHistoryFor'     => 'Migration history For ',
	'migRemoved'        => 'Rolling back: ',
	'migAdded'          => 'Running: ',

	'version'           => 'Version',
	'filename'          => 'Filename',
];
