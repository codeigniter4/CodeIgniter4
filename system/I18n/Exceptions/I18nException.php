<?php namespace CodeIgniter\I18n\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

class I18nException extends FrameworkException implements ExceptionInterface
{
	public static function forInvalidMonth(string $month)
	{
		return new self(lang('Time.invalidMonth', [$month]));
	}

	public static function forInvalidDay(string $day)
	{
		return new self(lang('Time.invalidDay', [$day]));
	}

	public static function forInvalidHour(string $hour)
	{
		return new self(lang('Time.invalidHour', [$hour]));
	}

	public static function forInvalidMinutes(string $minutes)
	{
		return new self(lang('Time.invalidMinutes', [$minutes]));
	}

	public static function forInvalidSeconds(string $seconds)
	{
		return new self(lang('Time.invalidSeconds', [$seconds]));
	}
}
