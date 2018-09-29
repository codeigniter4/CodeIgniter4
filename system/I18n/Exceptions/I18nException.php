<?php namespace CodeIgniter\I18n\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

class I18nException extends FrameworkException implements ExceptionInterface
{
	public static function forInvalidMonth(string $month)
	{
		return new static(lang('Time.invalidMonth', [$month]));
	}

	public static function forInvalidDay(string $day)
	{
		return new static(lang('Time.invalidDay', [$day]));
       }

       public static function forInvalidOverDay(string $lastDay, string $day)
	{
		return new static(lang('Time.invalidOverDay', [$lastDay, $day]));
	}

	public static function forInvalidHour(string $hour)
	{
		return new static(lang('Time.invalidHour', [$hour]));
	}

	public static function forInvalidMinutes(string $minutes)
	{
		return new static(lang('Time.invalidMinutes', [$minutes]));
	}

	public static function forInvalidSeconds(string $seconds)
	{
		return new static(lang('Time.invalidSeconds', [$seconds]));
	}
}
