<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Session language settings
return [
    'missingDatabaseTable'   => '`sessionSavePath` must have the table name for the Database Session Handler to work.',
    'invalidSavePath'        => 'Session: Configured save path "{0}" is not a directory, does not exist or cannot be created.',
    'writeProtectedSavePath' => 'Session: Configured save path "{0}" is not writable by the PHP process.',
    'emptySavePath'          => 'Session: No save path configured.',
    'invalidSavePathFormat'  => 'Session: Invalid Redis save path format: {0}',

    // @deprecated
    'invalidSameSiteSetting' => 'Session: The SameSite setting must be None, Lax, Strict, or a blank string. Given: {0}',
];
