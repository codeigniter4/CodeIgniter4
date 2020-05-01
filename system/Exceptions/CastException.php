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
				return new static(lang('Cast.jsonErrorDepth'));
			case JSON_ERROR_STATE_MISMATCH:
				return new static(lang('Cast.jsonErrorStateMismatch'));
			case JSON_ERROR_CTRL_CHAR:
				return new static(lang('Cast.jsonErrorCtrlChar'));
			case JSON_ERROR_SYNTAX:
				return new static(lang('Cast.jsonErrorSyntax'));
			case JSON_ERROR_UTF8:
				return new static(lang('Cast.jsonErrorUtf8'));
			default:
				return new static(lang('Cast.jsonErrorUnknown'));
		}
	}

}
