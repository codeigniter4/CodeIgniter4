<?php namespace CodeIgniter\Exceptions;

/**
 * Cast Exceptions.
 */

class CastException extends CriticalError
{

	/**
	 * Error code
	 *
	 * @var integer
	 */
	protected $code = 3;

	public static function forInvalidJsonFormatException(int $error)
	{
		switch($error)
		{
			case JSON_ERROR_DEPTH:
				throw new static(lang('Cast.jsonErrorDepth'));
			case JSON_ERROR_STATE_MISMATCH:
				throw new static(lang('Cast.jsonErrorStateMismatch'));
			case JSON_ERROR_CTRL_CHAR:
				throw new static(lang('Cast.jsonErrorCtrlChar'));
			case JSON_ERROR_SYNTAX:
				throw new static(lang('Cast.jsonErrorSyntax'));
			case JSON_ERROR_UTF8:
				throw new static(lang('Cast.jsonErrorUtf8'));
			default:
				throw new static(lang('Cast.jsonErrorUnknown'));
		}
	}

}
