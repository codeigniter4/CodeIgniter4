<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Mailer\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

class MailerException extends FrameworkException
{
	/**
	 * Thrown when specified handler was not found.
	 *
	 * @return static
	 */
	public static function forHandlerNotFound()
	{
		return new static(lang('Mailer.handlerNotFound'));
	}

	/**
	 * Thrown when specified handler is not supported.
	 *
	 * @param string $class
	 *
	 * @return static
	 */
	public static function forHandlerNotSupported(string $class)
	{
		return new static(lang('Mailer.handlerNotSupported', [$class]));
	}

	/**
	 * Thrown when an email address fails validation
	 *
	 * @param string|null $value
	 *
	 * @return static
	 */
	public static function forInvalidAddress(string $value = null)
	{
		return new static(lang('Mailer.invalidAddress', [$value]));
	}

	/**
	 * Thrown when an Email is missing the "From" header
	 *
	 * @return static
	 */
	public static function forNoFrom()
	{
		return new static(lang('Mailer.noFrom'));
	}

	/**
	 * Thrown when an Email has no "To", "Cc", and "Bcc" headers
	 *
	 * @return static
	 */
	public static function forNoRecipients()
	{
		return new static(lang('Mailer.noRecipients'));
	}

/*
	public static function forAttachmentMissing(string $file = null)
	{
		return new static(lang('Mailer.attachmentMissing', [$file]));
	}

	public static function forAttachmentUnreadable(string $file = null)
	{
		return new static(lang('Mailer.attachmentUnreadable', [$file]));
	}

	public static function forMustBeArray()
	{
		return new static(lang('Mailer.mustBeArray', []));
	}


	public static function forInvalidProtocol(string $value = null)
	{
		return new static(lang('Mailer.invalidProtocolRequested', [$value]));
	}

	public static function forSendFailure(string $protocol = '?')
	{
		return new static(lang('Mailer.SendFailure', [$protocol]));
	}

	public static function forNosocket(string $status = '?')
	{
		return new static(lang('Mailer.exitStatus', [$status]) . lang('Mailer.nosocket', []));
	}

	public static function forNoHostname()
	{
		return new static(lang('Mailer.noHostname', []));
	}

	public static function forSMTPError(string $reply = '?')
	{
		return new static(lang('Mailer.SMTPError', [$reply]));
	}

	public static function forNoSMTPAuth()
	{
		return new static(lang('Mailer.noSMTPAuth', []));
	}

	public static function forFailedSMTPLogin(string $reply = '?')
	{
		return new static(lang('Mailer.failedSMTPLogin', [$reply]));
	}

	public static function forSMTPAuthUsername(string $reply = '?')
	{
		return new static(lang('Mailer.SMTPAuthUsername', [$reply]));
	}

	public static function forSMTPAuthPassword(string $reply = '?')
	{
		return new static(lang('Mailer.SMTPAuthPassword', [$reply]));
	}

	public static function forSMTPDataFailure(string $data = '?')
	{
		return new static(lang('Mailer.SMTPDataFailure', [$data]));
	}
*/
}
