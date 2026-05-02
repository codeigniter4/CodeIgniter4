<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

// Cache language settings
return [
    'unableToWrite'        => 'Cache unable to write to "{0}".',
    'invalidHandler'       => 'Cache driver "{0}" is not a valid cache handler.',
    'invalidHandlers'      => 'Cache config must have an array of $validHandlers.',
    'noBackup'             => 'Cache config must have a handler and backupHandler set.',
    'handlerNotFound'      => 'Cache config has an invalid handler or backup handler specified.',
    'unsupportedLockStore' => 'The cache handler cannot provide a lock store with the current runtime client.',
];
