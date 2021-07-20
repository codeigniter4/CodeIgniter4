<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

// Migration language settings
return [
    // Migration Runner
    'missingTable'  => 'Migrations table must be set.',
    'disabled'      => 'Migrations have been loaded but are disabled or setup incorrectly.',
    'notFound'      => 'Migration file not found: ',
    'batchNotFound' => 'Target batch not found: ',
    'empty'         => 'No Migration files found',
    'gap'           => 'There is a gap in the migration sequence near version number: ',
    'classNotFound' => 'The migration class "%s" could not be found.',
    'missingMethod' => 'The migration class is missing an "%s" method.',

    // Migration Command
    'migHelpLatest'   => "\t\tMigrates database to latest available migration.",
    'migHelpCurrent'  => "\t\tMigrates database to version set as 'current' in configuration.",
    'migHelpVersion'  => "\tMigrates database to version {v}.",
    'migHelpRollback' => "\tRuns all migrations 'down' to version 0.",
    'migHelpRefresh'  => "\t\tUninstalls and re-runs all migrations to freshen database.",
    'migHelpSeed'     => "\tRuns the seeder named [name].",
    'migCreate'       => "\tCreates a new migration named [name]",
    'nameMigration'   => 'Name the migration file',
    'migNumberError'  => 'Migration number must be three digits, and there must not be any gaps in the sequence.',
    'rollBackConfirm' => 'Are you sure you want to rollback?',
    'refreshConfirm'  => 'Are you sure you want to refresh?',

    'latest'            => 'Running all new migrations...',
    'generalFault'      => 'Migration failed!',
    'migInvalidVersion' => 'Invalid version number provided.',
    'toVersionPH'       => 'Migrating to version %s...',
    'toVersion'         => 'Migrating to current version...',
    'rollingBack'       => 'Rolling back migrations to batch: ',
    'noneFound'         => 'No migrations were found.',
    'migSeeder'         => 'Seeder name',
    'migMissingSeeder'  => 'You must provide a seeder name.',
    'nameSeeder'        => 'Name the seeder file',
    'removed'           => 'Rolling back: ',
    'added'             => 'Running: ',

    // Migrate Status
    'namespace' => 'Namespace',
    'filename'  => 'Filename',
    'version'   => 'Version',
    'group'     => 'Group',
    'on'        => 'Migrated On: ',
    'batch'     => 'Batch',
];
