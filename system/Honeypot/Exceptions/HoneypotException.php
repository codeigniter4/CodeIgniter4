<?php namespace CodeIgniter\Honeypot\Exceptions;

use CodeIgniter\Exceptions\ConfigException;
use CodeIgniter\Exceptions\ExceptionInterface;

class HoneypotException extends ConfigException implements ExceptionInterface
{
	public static function forNoTemplate()
	{
		return new static(lang('Honeypot.noTemplate'));
	}

	public static function forNoNameField()
	{
		return new static(lang('Honeypot.noNameField'));
	}

	public static function forNoHiddenValue()
	{
		return new static(lang('Honeypot.noHiddenValue'));
	}

	public static function isBot()
	{
		return new static(lang('Honeypot.theClientIsABot'));
	}

}
