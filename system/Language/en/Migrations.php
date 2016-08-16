<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

return [
	// Migration Runner
	'migMissingTable'  => 'Migrations table must be set.',
	'migInvalidType'   => 'An invalid migration numbering type was specified: ',
	'migDisabled'      => 'Migrations have been loaded but are disabled or setup incorrectly.',
	'migNotFound'      => 'Migration file not found: ',
	'migGap'           => 'There is a gap in the migration sequence near version number: ',
	'migClassNotFound' => 'The migration class "%s" could not be found.',
	'migMissingMethod' => 'The migration class is missing an "%s" method.',
	'migMultiple'      => 'There are multiple migrations with the same version number: ',

	// Migration Command
	'migHelpLatest'    => "\t\tMigrates database to latest available migration.",
	'migHelpCurrent'   => "\t\tMigrates database to version set as 'current' in configuration.",
	'migHelpVersion'   => "\tMigrates database to version {v}.",
	'migHelpRollback'  => "\tRuns all migrations 'down' to version 0.",
	'migHelpRefresh'   => "\t\tUninstalls and re-runs all migrations to freshen database.",
	'migHelpSeed'      => "\tRuns the seeder named [name].",

	'migToLatest'       => 'Migrating to latest version...',
	'migInvalidVersion' => 'Invalid version number provided.',
	'migToVersionPH'    => 'Migrating to version %s...',
	'migToVersion'      => 'Migrating to current version...',
	'migRollingBack'    => "Rolling back all migrations...",
	'migNoneFound'      => 'No migrations were found.',
	'migOn'             => 'Migrated On',
	'migSeeder'         => 'Seeder name',
	'migMissingSeeder'  => 'You must provide a seeder name.',

	'version'  => 'Version',
	'filename' => 'Filename',
];
