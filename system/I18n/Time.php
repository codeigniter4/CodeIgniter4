<?php namespace CodeIgniter\I18n;

use Locale;
use DateTime;
use DateTimeZone;
use IntlDateFormatter;

/**
 * Class Time
 *
 * A localized date/time package inspired
 * by Nesbot/Carbon and CakePHP/Chronos.
 *
 * Requires the intl PHP extension.
 *
 * @package CodeIgniter\I18n
 */
class Time extends DateTime
{
	/**
	 * @var string
	 */
	protected $timezone;

	/**
	 * @var string
	 */
	protected $locale;

	/**
	 * Format to use when displaying datetime through __toString
	 * @var int
	 */
	protected $toStringFormat = IntlDateFormatter::SHORT;

	/**
	 * Used to check time string to determine if it is relative time or not....
	 *
	 * @var string
	 */
	protected static $relativePattern = '/this|next|last|tomorrow|yesterday|midnight|today|[+-]|first|last|ago/i';

	//--------------------------------------------------------------------
	// Constructors
	//--------------------------------------------------------------------

	public function __construct(string $time=null, $timezone=null, string $locale=null)
	{
		$timezone = ! empty($timezone) ? $timezone : date_default_timezone_get();
		$this->timezone = $timezone instanceof DateTimeZone ? $timezone : new DateTimeZone($timezone);

		// If no locale was provided, grab it from Locale (set by IncomingRequest for web requests)
		$this->locale = ! empty($locale) ? $locale : Locale::getDefault();

		if (! empty($time))
		{
			// If the time string was a relative string (i.e. 'next Tuesday')
			// then we need to adjust the time going in so that we have a current
			// timezone to work with.
			if (is_string($time) && static::hasRelativeKeywords($time))
			{
				$instance = new DateTime('now', $this->timezone);
				$instance->modify($time);

				$time = $instance->format('Y-m-d H:i:s');
			}
		}

		return parent::__construct($time, $this->timezone);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a new Time instance with the timezone set.
	 *
	 * @param string|DateTimeZone|null $timezone
	 * @param string|null $locale
	 *
	 * @return \CodeIgniter\I18n\Time
	 */
	public static function now($timezone=null, string $locale=null)
	{
		return new Time(null, $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a new Time instance while parsing a datetime string.
	 *
	 * Example:
	 *  $time = Time::parse('first day of December 2008');
	 *
	 * @param string      $datetime
	 * @param string|null $timezone
	 * @param string|null $locale
	 *
	 * @return \CodeIgniter\I18n\Time
	 */
	public static function parse(string $datetime, $timezone=null, string $locale=null)
	{
		return new Time($datetime, $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Return a new time with the time set to midnight.
	 *
	 * @param null        $timezone
	 * @param string|null $locale
	 *
	 * @return \CodeIgniter\I18n\Time
	 */
	public static function today($timezone=null, string $locale=null)
	{
		return new Time(date('Y-m-d 00:00:00'), $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an instance set to midnight yesterday morning.
	 *
	 * @param null        $timezone
	 * @param string|null $locale
	 *
	 * @return \CodeIgniter\I18n\Time
	 */
	public static function yesterday($timezone=null, string $locale=null)
	{
		return new Time(date('Y-m-d 00:00:00', strtotime('-1 day')), $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an instance set to midnight tomorrow morning.
	 *
	 * @param null        $timezone
	 * @param string|null $locale
	 *
	 * @return \CodeIgniter\I18n\Time
	 */
	public static function tomorrow($timezone=null, string $locale=null)
	{
		return new Time(date('Y-m-d 00:00:00', strtotime('+1 day')), $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a new instance based on the year, month and day. If any of those three
	 * are left empty, will default to the current value.
	 *
	 * @param int|null $year
	 * @param int|null $month
	 * @param int|null $day
	 * @param          $timezone
	 * @param string   $locale
	 *
	 * @return \CodeIgniter\I18n\Time
	 */
	public static function createFromDate(int $year=null, int $month=null, int $day=null, $timezone=null, string $locale=null)
	{
		return static::create($year, $month, $day, null, null, null, $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a new instance with the date set to today, and the time set to the values passed in.
	 *
	 * @param int|null    $hour
	 * @param int|null    $minutes
	 * @param int|null    $seconds
	 * @param null        $timezone
	 * @param string|null $locale
	 *
	 * @return \CodeIgniter\I18n\Time
	 */
	public static function createFromTime(int $hour=null, int $minutes=null, int $seconds=null, $timezone=null, string $locale=null)
	{
		return static::create(null, null, null, $hour, $minutes, $seconds, $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a new instance with the date time values individually set.
	 *
	 * @param int|null    $year
	 * @param int|null    $month
	 * @param int|null    $day
	 * @param int|null    $hour
	 * @param int|null    $minutes
	 * @param int|null    $seconds
	 * @param null        $timezone
	 * @param string|null $locale
	 *
	 * @return \CodeIgniter\I18n\Time
	 */
	public static function create(int $year=null, int $month=null, int $day=null, int $hour=null, int $minutes=null, int $seconds=null, $timezone=null, string $locale=null)
	{
		$year  = is_null($year)  ? date('Y') : $year;
		$month = is_null($month) ? date('m') : $month;
		$day   = is_null($day)   ? date('d') : $day;
		$hour    = empty($hour)    ? 0 : $hour;
		$minutes = empty($minutes) ? 0 : $minutes;
		$seconds = empty($seconds) ? 0 : $seconds;

		return new Time(date("Y-m-d H:i:s", strtotime("{$year}-{$month}-{$day} {$hour}:{$minutes}:{$seconds}")), $timezone, $locale);
	}

	//--------------------------------------------------------------------

	/**
	 * Converts the current instance to a mutable DateTime object.
	 *
	 * @return \DateTime
	 */
	public function toDateTime()
	{
		$dateTime = new \DateTime(null, $this->getTimezone());
		$dateTime->setTimestamp($this->getTimestamp());

		return $dateTime;
	}

	//--------------------------------------------------------------------

	/**
	 * Check a time string to see if it includes a relative date (like 'next Tuesday').
	 *
	 * @param string $time
	 *
	 * @return bool
	 */
	protected static function hasRelativeKeywords(string $time): bool
	{
		// skip common format with a '-' in it
		if (preg_match('/[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}/', $time) !== 1)
		{
			return preg_match(static::$relativePattern, $time) > 0;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the value of this instance in the format 'Y-m-d H:i:s'
	 */
	public function toDateTimeString()
	{
		return $this->format('Y-m-d H:i:s');
	}

	//--------------------------------------------------------------------

	/**
	 * Outputs a short format version of the datetime.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return IntlDateFormatter::formatObject($this->toDateTime(), $this->toStringFormat, $this->locale);
	}
}
