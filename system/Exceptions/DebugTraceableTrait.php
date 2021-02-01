<?php

namespace CodeIgniter\Exceptions;

use Throwable;

/**
 * This trait provides framework exceptions the ability to pinpoint
 * accurately where the exception was raised rather than instantiated.
 *
 * This is used primarily for factory-instantiated exceptions.
 */
trait DebugTraceableTrait
{
	/**
	 * Tweaks the exception's constructor to assign the file/line to where
	 * it is actually raised rather than were it is instantiated.
	 *
	 * @param string         $message
	 * @param integer        $code
	 * @param Throwable|null $previous
	 */
	public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);

		$trace = $this->getTrace()[0];

		if (isset($trace['class']) && $trace['class'] === static::class)
		{
			['line' => $this->line, 'file' => $this->file] = $trace;
		}
	}
}
