<?php namespace CodeIgniter\I18n;

use Locale;
use DateTime;
use DateTimeZone;
use IntlDateFormatter;

/**
 * Class Time
 *
 * A wrapper around IntlCalendar to provide a slightly nicer UX.
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
	 * words used to determine if a time string needs to be modified.
	 *
	 * @var array
	 */
	protected static $relativeKeywords = [
		'+', '-', 'ago', 'first', 'last', 'next', 'this', 'today', 'tomorrow', 'yesterday',
	];

	//--------------------------------------------------------------------
	// Constructors
	//--------------------------------------------------------------------

	public function __construct(string $time=null, string $timezone=null, string $locale=null)
	{
		$timezone = ! empty($timezone) ? $timezone : date_default_timezone_get();
		$this->timezone = new DateTimeZone($timezone);

		// If no locale was provided, grab it from Locale (set by IncomingRequest for web requests)
		$this->locale = ! empty($locale) ? $locale : Locale::getDefault();

		if (! empty($time))
		{
			if (static::hasRelativeKeywords($time))
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
	 * @param string|null $timezone
	 * @param string|null $locale
	 *
	 * @return \CodeIgniter\I18n\Time
	 */
	public static function now(string $timezone=null, string $locale=null)
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
	public static function parse(string $datetime, string $timezone=null, string $locale=null)
	{
		return new Time($datetime, $timezone, $locale);
	}

	//--------------------------------------------------------------------

	public static function hasRelativeKeywords(string $time)
	{

	}

	/**
	 * Outputs a short format version of the datetime.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return IntlDateFormatter::formatObject($this, $this->toStringFormat, $this->locale);
	}
}
