<?php
namespace CodeIgniter\Emails\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;

class EmailException extends \RuntimeException implements ExceptionInterface
{

	public static function forAttachmentMissing(string $file = null)
	{
		return new static(lang('Email.attachmentMissing', [$file]));
	}

	public static function forAttachmentUnreadable(string $file = null)
	{
		return new static(lang('Email.attachmentUnreadable', [$file]));
	}

	public static function forMustBeArray()
	{
		return new static(lang('Email.mustBeArray', []));
	}

	public static function forInvalidAddress(string $value = null)
	{
		return new static(lang('Email.invalidAddress', [$value]));
	}

	public static function forNoFrom()
	{
		return new static(lang('Email.noFrom', []));
	}

	public static function forNoRecipients()
	{
		return new static(lang('Email.noRecipients', []));
	}

	public static function forSendFailure(string $protocol = '?')
	{
		return new static(lang('Email.SendFailure', [$protocol]));
	}

	public static function forNosocket(string $status = '?')
	{
		return new static(lang('Email.exitStatus', [$status]) .
				lang('Email.nosocket', []));
	}

	public static function forNoHostname()
	{
		return new static(lang('Email.noHostname', []));
	}

	public static function forSMTPError(string $reply = '?')
	{
		return new static(lang('Email.SMTPError', [$reply]));
	}

	public static function forNoSMTPAuth()
	{
		return new static(lang('Email.noSMTPAuth', []));
	}

	public static function forFailedSMTPLogin(string $reply = '?')
	{
		return new static(lang('Email.failedSMTPLogin', [$reply]));
	}

	public static function forSMTPAuthUsername(string $reply = '?')
	{
		return new static(lang('Email.SMTPAuthUsername', [$reply]));
	}

	public static function forSMTPAuthPassword(string $reply = '?')
	{
		return new static(lang('Email.SMTPAuthPassword', [$reply]));
	}

	public static function forSMTPDataFailure(string $data = '?')
	{
		return new static(lang('Email.SMTPDataFailure', [$data]));
	}

}
