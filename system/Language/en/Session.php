<?php

/**
 * Session language strings.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 *
 * @codeCoverageIgnore
 */

return [
   'missingDatabaseTable'   => '`sessionSavePath` must have the table name for the Database Session Handler to work.',
   'invalidSavePath'        => 'Session: Configured save path "{0}" is not a directory, does not exist or cannot be created.',
   'writeProtectedSavePath' => 'Session: Configured save path "{0}" is not writable by the PHP process.',
   'emptySavePath'          => 'Session: No save path configured.',
   'invalidSavePathFormat'  => 'Session: Invalid Redis save path format: {0}',
   'invalidSameSiteSetting' => 'Session: The SameSite setting must be None, Lax, Strict, or a blank string. Given: {0}',
];
