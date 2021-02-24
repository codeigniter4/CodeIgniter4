<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Log\Handlers;

/**
 * Log error messages to PHP error log
 */
class ErrorlogHandler extends BaseHandler
{

	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function __construct(array $config = [])
	{
		parent::__construct($config);
	}

	//--------------------------------------------------------------------

	/**
	 * Handles logging the message.
	 * If the handler returns false, then execution of handlers
	 * will stop. Any handlers that have not run, yet, will not
	 * be run.
	 *
	 * @param string $level
	 * @param string $message
	 *
	 * @return boolean
	 */
	public function handle($level, $message): bool
	{
		$msg = strtoupper($level) . ' --> ' . $message . "\n";

		return error_log($msg);
	}
}
