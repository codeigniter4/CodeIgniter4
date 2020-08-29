<?php namespace CodeIgniter\Test;

use CodeIgniter\Log\Logger;

class TestLogger extends Logger
{

	protected static $op_logs = [];

	//--------------------------------------------------------------------

	/**
	 * The log method is overridden so that we can store log history during
	 * the tests to allow us to check ->assertLogged() methods.
	 *
	 * @param string $level
	 * @param string $message
	 * @param array  $context
	 *
	 * @return boolean
	 */
	public function log($level, $message, array $context = []): bool
	{
		// While this requires duplicate work, we want to ensure
		// we have the final message to test against.
		$log_message = $this->interpolate($message, $context);

		// Determine the file and line by finding the first
		// backtrace that is not part of our logging system.
		$trace = debug_backtrace();
		$file  = null;

		foreach ($trace as $row)
		{
			if (! in_array($row['function'], ['log', 'log_message'], true))
			{
				$file = basename($row['file'] ?? '');
				break;
			}
		}

		self::$op_logs[] = [
				  'level'   => $level,
				  'message' => $log_message,
				  'file'    => $file,
			  ];

		// Let the parent do it's thing.
		return parent::log($level, $message, $context);
	}

	//--------------------------------------------------------------------

	/**
	 * Used by CIUnitTestCase class to provide ->assertLogged() methods.
	 *
	 * @param string $level
	 * @param string $message
	 *
	 * @return boolean
	 */
	public static function didLog(string $level, $message)
	{
		foreach (self::$op_logs as $log)
		{
			if (strtolower($log['level']) === strtolower($level) && $message === $log['message'])
			{
				return true;
			}
		}

		return false;
	}

	//--------------------------------------------------------------------
	// Expose cleanFileNames()
	public function cleanup($file)
	{
		return $this->cleanFileNames($file);
	}

}
