<?php namespace CodeIgniter\Exceptions;

/**
 * Cast Exceptions.
 */
class CastException extends CriticalError
{

	/**
	 * Error code
	 * @var int
	 */
	protected $code = 3;

	public static function forInvalidJsonFormatException(int $error)
	{	
		switch($error)
		{
			case JSON_ERROR_DEPTH:
				throw new static(lang('Cast.jsonErrorDepth'));
				break;
			case JSON_ERROR_STATE_MISMATCH:
				throw new static(lang('Cast.jsonErrorStateMismatch'));
				break;
			case JSON_ERROR_CTRL_CHAR:
				throw new static(lang('Cast.jsonErrorCtrlChar'));
				break;
			case JSON_ERROR_SYNTAX:
				throw new static(lang('Cast.jsonErrorSyntax'));
				break;
			case JSON_ERROR_UTF8:
				throw new static(lang('Cast.jsonErrorUtf8'));
				break;
			default:
				throw new static(lang('Cast.jsonErrorUnknown'));
		}

	}

}
