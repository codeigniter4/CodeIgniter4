<?php

/**
 * Session language strings.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 *
 * @codeCoverageIgnore
 */

return [
   'missingDatabaseTable'   => '`sessionSavePath` must have the table name for the Database Session Handler to work.',
   'invalidSavePath'        => "Session: Configured save path '{0}' is not a directory, doesn't exist or cannot be created.",
   'writeProtectedSavePath' => "Session: Configured save path '{0}' is not writable by the PHP process.",
   'emptySavePath'          => 'Session: No save path configured.',
   'invalidSavePathFormat'  => 'Session: Invalid Redis save path format: {0}',
];
