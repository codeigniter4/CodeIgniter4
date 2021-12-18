<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\I18n;

use CodeIgniter\I18n\Exceptions\I18nException;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use IntlCalendar;
use IntlDateFormatter;
use Locale;
use ReturnTypeWillChange;

/**
 * A localized date/time package inspired
 * by Nesbot/Carbon and CakePHP/Chronos.
 *
 * Requires the intl PHP extension.
 *
 * @property string $date
 */
class Time extends DateTime
{
    /**
     * @var DateTimeZone
     */
    protected $timezone;

    /**
     * @var string
     */
    protected $locale;

    /**
     * Format to use when displaying datetime through __toString
     *
     * @var string
     */
    protected $toStringFormat = 'yyyy-MM-dd HH:mm:ss';

    /**
     * Used to check time string to determine if it is relative time or not....
     *
     * @var string
     */
    protected static $relativePattern = '/this|next|last|tomorrow|yesterday|midnight|today|[+-]|first|last|ago/i';

    /**
     * @var DateTimeInterface|static|null
     */
    protected static $testNow;

    //--------------------------------------------------------------------
    // Constructors
    //--------------------------------------------------------------------

    /**
     * Time constructor.
     *
     * @param DateTimeZone|string|null $timezone
     *
     * @throws Exception
     */
    public function __construct(?string $time = null, $timezone = null, ?string $locale = null)
    {
        $this->locale = $locale ?: Locale::getDefault();

        $time = $time ?? '';

        // If a test instance has been provided, use it instead.
        if ($time === '' && static::$testNow instanceof self) {
            $timezone = $timezone ?: static::$testNow->getTimezone();
            $time     = (string) static::$testNow->toDateTimeString();
        }

        $timezone       = $timezone ?: date_default_timezone_get();
        $this->timezone = $timezone instanceof DateTimeZone ? $timezone : new DateTimeZone($timezone);

        // If the time string was a relative string (i.e. 'next Tuesday')
        // then we need to adjust the time going in so that we have a current
        // timezone to work with.
        if ($time !== '' && static::hasRelativeKeywords($time)) {
            $instance = new DateTime('now', $this->timezone);
            $instance->modify($time);
            $time = $instance->format('Y-m-d H:i:s');
        }

        parent::__construct($time, $this->timezone);
    }

    /**
     * Returns a new Time instance with the timezone set.
     *
     * @param DateTimeZone|string|null $timezone
     *
     * @throws Exception
     *
     * @return Time
     */
    public static function now($timezone = null, ?string $locale = null)
    {
        return new self(null, $timezone, $locale);
    }

    /**
     * Returns a new Time instance while parsing a datetime string.
     *
     * Example:
     *  $time = Time::parse('first day of December 2008');
     *
     * @param DateTimeZone|string|null $timezone
     *
     * @throws Exception
     *
     * @return Time
     */
    public static function parse(string $datetime, $timezone = null, ?string $locale = null)
    {
        return new self($datetime, $timezone, $locale);
    }

    /**
     * Return a new time with the time set to midnight.
     *
     * @param DateTimeZone|string|null $timezone
     *
     * @throws Exception
     *
     * @return Time
     */
    public static function today($timezone = null, ?string $locale = null)
    {
        return new self(date('Y-m-d 00:00:00'), $timezone, $locale);
    }

    /**
     * Returns an instance set to midnight yesterday morning.
     *
     * @param DateTimeZone|string|null $timezone
     *
     * @throws Exception
     *
     * @return Time
     */
    public static function yesterday($timezone = null, ?string $locale = null)
    {
        return new self(date('Y-m-d 00:00:00', strtotime('-1 day')), $timezone, $locale);
    }

    /**
     * Returns an instance set to midnight tomorrow morning.
     *
     * @param DateTimeZone|string|null $timezone
     *
     * @throws Exception
     *
     * @return Time
     */
    public static function tomorrow($timezone = null, ?string $locale = null)
    {
        return new self(date('Y-m-d 00:00:00', strtotime('+1 day')), $timezone, $locale);
    }

    /**
     * Returns a new instance based on the year, month and day. If any of those three
     * are left empty, will default to the current value.
     *
     * @param DateTimeZone|string|null $timezone
     *
     * @throws Exception
     *
     * @return Time
     */
    public static function createFromDate(?int $year = null, ?int $month = null, ?int $day = null, $timezone = null, ?string $locale = null)
    {
        return static::create($year, $month, $day, null, null, null, $timezone, $locale);
    }

    /**
     * Returns a new instance with the date set to today, and the time set to the values passed in.
     *
     * @param DateTimeZone|string|null $timezone
     *
     * @throws Exception
     *
     * @return Time
     */
    public static function createFromTime(?int $hour = null, ?int $minutes = null, ?int $seconds = null, $timezone = null, ?string $locale = null)
    {
        return static::create(null, null, null, $hour, $minutes, $seconds, $timezone, $locale);
    }

    /**
     * Returns a new instance with the date time values individually set.
     *
     * @param DateTimeZone|string|null $timezone
     *
     * @throws Exception
     *
     * @return Time
     */
    public static function create(?int $year = null, ?int $month = null, ?int $day = null, ?int $hour = null, ?int $minutes = null, ?int $seconds = null, $timezone = null, ?string $locale = null)
    {
        $year    = $year ?? date('Y');
        $month   = $month ?? date('m');
        $day     = $day ?? date('d');
        $hour    = empty($hour) ? 0 : $hour;
        $minutes = empty($minutes) ? 0 : $minutes;
        $seconds = empty($seconds) ? 0 : $seconds;

        return new self(date('Y-m-d H:i:s', strtotime("{$year}-{$month}-{$day} {$hour}:{$minutes}:{$seconds}")), $timezone, $locale);
    }

    /**
     * Provides a replacement for DateTime's own createFromFormat function, that provides
     * more flexible timeZone handling
     *
     * @param string                   $format
     * @param string                   $datetime
     * @param DateTimeZone|string|null $timezone
     *
     * @throws Exception
     *
     * @return Time
     */
    #[ReturnTypeWillChange]
    public static function createFromFormat($format, $datetime, $timezone = null)
    {
        if (! $date = parent::createFromFormat($format, $datetime)) {
            throw I18nException::forInvalidFormat($format);
        }

        return new self($date->format('Y-m-d H:i:s'), $timezone);
    }

    /**
     * Returns a new instance with the datetime set based on the provided UNIX timestamp.
     *
     * @param DateTimeZone|string|null $timezone
     *
     * @throws Exception
     *
     * @return Time
     */
    public static function createFromTimestamp(int $timestamp, $timezone = null, ?string $locale = null)
    {
        return new self(gmdate('Y-m-d H:i:s', $timestamp), $timezone ?? 'UTC', $locale);
    }

    /**
     * Takes an instance of DateTimeInterface and returns an instance of Time with it's same values.
     *
     * @throws Exception
     *
     * @return Time
     */
    public static function createFromInstance(DateTimeInterface $dateTime, ?string $locale = null)
    {
        $date     = $dateTime->format('Y-m-d H:i:s');
        $timezone = $dateTime->getTimezone();

        return new self($date, $timezone, $locale);
    }

    /**
     * Takes an instance of DateTime and returns an instance of Time with it's same values.
     *
     * @throws Exception
     *
     * @return Time
     *
     * @deprecated         Use createFromInstance() instead
     * @codeCoverageIgnore
     */
    public static function instance(DateTime $dateTime, ?string $locale = null)
    {
        return self::createFromInstance($dateTime, $locale);
    }

    /**
     * Converts the current instance to a mutable DateTime object.
     *
     * @throws Exception
     *
     * @return DateTime
     */
    public function toDateTime()
    {
        $dateTime = new DateTime('', $this->getTimezone());
        $dateTime->setTimestamp(parent::getTimestamp());

        return $dateTime;
    }

    //--------------------------------------------------------------------
    // For Testing
    //--------------------------------------------------------------------

    /**
     * Creates an instance of Time that will be returned during testing
     * when calling 'Time::now' instead of the current time.
     *
     * @param DateTimeInterface|string|Time|null $datetime
     * @param DateTimeZone|string|null           $timezone
     *
     * @throws Exception
     */
    public static function setTestNow($datetime = null, $timezone = null, ?string $locale = null)
    {
        // Reset the test instance
        if ($datetime === null) {
            static::$testNow = null;

            return;
        }

        // Convert to a Time instance
        if (is_string($datetime)) {
            $datetime = new self($datetime, $timezone, $locale);
        } elseif ($datetime instanceof DateTimeInterface && ! $datetime instanceof self) {
            $datetime = new self($datetime->format('Y-m-d H:i:s'), $timezone);
        }

        static::$testNow = $datetime;
    }

    /**
     * Returns whether we have a testNow instance saved.
     */
    public static function hasTestNow(): bool
    {
        return static::$testNow !== null;
    }

    //--------------------------------------------------------------------
    // Getters
    //--------------------------------------------------------------------

    /**
     * Returns the localized Year
     *
     * @throws Exception
     */
    public function getYear(): string
    {
        return $this->toLocalizedString('y');
    }

    /**
     * Returns the localized Month
     *
     * @throws Exception
     */
    public function getMonth(): string
    {
        return $this->toLocalizedString('M');
    }

    /**
     * Return the localized day of the month.
     *
     * @throws Exception
     */
    public function getDay(): string
    {
        return $this->toLocalizedString('d');
    }

    /**
     * Return the localized hour (in 24-hour format).
     *
     * @throws Exception
     */
    public function getHour(): string
    {
        return $this->toLocalizedString('H');
    }

    /**
     * Return the localized minutes in the hour.
     *
     * @throws Exception
     */
    public function getMinute(): string
    {
        return $this->toLocalizedString('m');
    }

    /**
     * Return the localized seconds
     *
     * @throws Exception
     */
    public function getSecond(): string
    {
        return $this->toLocalizedString('s');
    }

    /**
     * Return the index of the day of the week
     *
     * @throws Exception
     */
    public function getDayOfWeek(): string
    {
        return $this->toLocalizedString('c');
    }

    /**
     * Return the index of the day of the year
     *
     * @throws Exception
     */
    public function getDayOfYear(): string
    {
        return $this->toLocalizedString('D');
    }

    /**
     * Return the index of the week in the month
     *
     * @throws Exception
     */
    public function getWeekOfMonth(): string
    {
        return $this->toLocalizedString('W');
    }

    /**
     * Return the index of the week in the year
     *
     * @throws Exception
     */
    public function getWeekOfYear(): string
    {
        return $this->toLocalizedString('w');
    }

    /**
     * Returns the age in years from the "current" date and 'now'
     *
     * @throws Exception
     *
     * @return int
     */
    public function getAge()
    {
        $now  = self::now()->getTimestamp();
        $time = $this->getTimestamp();

        // future dates have no age
        return max(0, date('Y', $now) - date('Y', $time));
    }

    /**
     * Returns the number of the current quarter for the year.
     *
     * @throws Exception
     */
    public function getQuarter(): string
    {
        return $this->toLocalizedString('Q');
    }

    /**
     * Are we in daylight savings time currently?
     */
    public function getDst(): bool
    {
        return $this->format('I') === '1'; // 1 if Daylight Saving Time, 0 otherwise.
    }

    /**
     * Returns boolean whether the passed timezone is the same as
     * the local timezone.
     */
    public function getLocal(): bool
    {
        $local = date_default_timezone_get();

        return $local === $this->timezone->getName();
    }

    /**
     * Returns boolean whether object is in UTC.
     */
    public function getUtc(): bool
    {
        return $this->getOffset() === 0;
    }

    /**
     * Returns the name of the current timezone.
     */
    public function getTimezoneName(): string
    {
        return $this->timezone->getName();
    }

    //--------------------------------------------------------------------
    // Setters
    //--------------------------------------------------------------------

    /**
     * Sets the current year for this instance.
     *
     * @param int|string $value
     *
     * @throws Exception
     *
     * @return Time
     */
    public function setYear($value)
    {
        return $this->setValue('year', $value);
    }

    /**
     * Sets the month of the year.
     *
     * @param int|string $value
     *
     * @throws Exception
     *
     * @return Time
     */
    public function setMonth($value)
    {
        if (is_numeric($value) && ($value < 1 || $value > 12)) {
            throw I18nException::forInvalidMonth($value);
        }

        if (is_string($value) && ! is_numeric($value)) {
            $value = date('m', strtotime("{$value} 1 2017"));
        }

        return $this->setValue('month', $value);
    }

    /**
     * Sets the day of the month.
     *
     * @param int|string $value
     *
     * @throws Exception
     *
     * @return Time
     */
    public function setDay($value)
    {
        if ($value < 1 || $value > 31) {
            throw I18nException::forInvalidDay($value);
        }

        $date    = $this->getYear() . '-' . $this->getMonth();
        $lastDay = date('t', strtotime($date));
        if ($value > $lastDay) {
            throw I18nException::forInvalidOverDay($lastDay, $value);
        }

        return $this->setValue('day', $value);
    }

    /**
     * Sets the hour of the day (24 hour cycle)
     *
     * @param int|string $value
     *
     * @throws Exception
     *
     * @return Time
     */
    public function setHour($value)
    {
        if ($value < 0 || $value > 23) {
            throw I18nException::forInvalidHour($value);
        }

        return $this->setValue('hour', $value);
    }

    /**
     * Sets the minute of the hour
     *
     * @param int|string $value
     *
     * @throws Exception
     *
     * @return Time
     */
    public function setMinute($value)
    {
        if ($value < 0 || $value > 59) {
            throw I18nException::forInvalidMinutes($value);
        }

        return $this->setValue('minute', $value);
    }

    /**
     * Sets the second of the minute.
     *
     * @param int|string $value
     *
     * @throws Exception
     *
     * @return Time
     */
    public function setSecond($value)
    {
        if ($value < 0 || $value > 59) {
            throw I18nException::forInvalidSeconds($value);
        }

        return $this->setValue('second', $value);
    }

    /**
     * Helper method to do the heavy lifting of the 'setX' methods.
     *
     * @param int $value
     *
     * @throws Exception
     *
     * @return Time
     */
    protected function setValue(string $name, $value)
    {
        [$year, $month, $day, $hour, $minute, $second] = explode('-', $this->format('Y-n-j-G-i-s'));

        ${$name} = $value;

        return self::create(
            (int) $year,
            (int) $month,
            (int) $day,
            (int) $hour,
            (int) $minute,
            (int) $second,
            $this->getTimezoneName(),
            $this->locale
        );
    }

    /**
     * Returns a new instance with the revised timezone.
     *
     * @param DateTimeZone|string $timezone
     *
     * @throws Exception
     *
     * @return Time
     */
    #[ReturnTypeWillChange]
    public function setTimezone($timezone)
    {
        $timezone = $timezone instanceof DateTimeZone ? $timezone : new DateTimeZone($timezone);

        return self::createFromInstance($this->toDateTime()->setTimezone($timezone), $this->locale);
    }

    /**
     * Returns a new instance with the date set to the new timestamp.
     *
     * @param int $timestamp
     *
     * @throws Exception
     *
     * @return Time
     */
    #[ReturnTypeWillChange]
    public function setTimestamp($timestamp)
    {
        $time = date('Y-m-d H:i:s', $timestamp);

        return self::parse($time, $this->timezone, $this->locale);
    }

    //--------------------------------------------------------------------
    // Add/Subtract
    //--------------------------------------------------------------------

    /**
     * Returns a new Time instance with $seconds added to the time.
     *
     * @return static
     */
    public function addSeconds(int $seconds)
    {
        $time = clone $this;

        return $time->add(DateInterval::createFromDateString("{$seconds} seconds"));
    }

    /**
     * Returns a new Time instance with $minutes added to the time.
     *
     * @return static
     */
    public function addMinutes(int $minutes)
    {
        $time = clone $this;

        return $time->add(DateInterval::createFromDateString("{$minutes} minutes"));
    }

    /**
     * Returns a new Time instance with $hours added to the time.
     *
     * @return static
     */
    public function addHours(int $hours)
    {
        $time = clone $this;

        return $time->add(DateInterval::createFromDateString("{$hours} hours"));
    }

    /**
     * Returns a new Time instance with $days added to the time.
     *
     * @return static
     */
    public function addDays(int $days)
    {
        $time = clone $this;

        return $time->add(DateInterval::createFromDateString("{$days} days"));
    }

    /**
     * Returns a new Time instance with $months added to the time.
     *
     * @return static
     */
    public function addMonths(int $months)
    {
        $time = clone $this;

        return $time->add(DateInterval::createFromDateString("{$months} months"));
    }

    /**
     * Returns a new Time instance with $years added to the time.
     *
     * @return static
     */
    public function addYears(int $years)
    {
        $time = clone $this;

        return $time->add(DateInterval::createFromDateString("{$years} years"));
    }

    /**
     * Returns a new Time instance with $seconds subtracted from the time.
     *
     * @return static
     */
    public function subSeconds(int $seconds)
    {
        $time = clone $this;

        return $time->sub(DateInterval::createFromDateString("{$seconds} seconds"));
    }

    /**
     * Returns a new Time instance with $minutes subtracted from the time.
     *
     * @return static
     */
    public function subMinutes(int $minutes)
    {
        $time = clone $this;

        return $time->sub(DateInterval::createFromDateString("{$minutes} minutes"));
    }

    /**
     * Returns a new Time instance with $hours subtracted from the time.
     *
     * @return static
     */
    public function subHours(int $hours)
    {
        $time = clone $this;

        return $time->sub(DateInterval::createFromDateString("{$hours} hours"));
    }

    /**
     * Returns a new Time instance with $days subtracted from the time.
     *
     * @return static
     */
    public function subDays(int $days)
    {
        $time = clone $this;

        return $time->sub(DateInterval::createFromDateString("{$days} days"));
    }

    /**
     * Returns a new Time instance with $months subtracted from the time.
     *
     * @return static
     */
    public function subMonths(int $months)
    {
        $time = clone $this;

        return $time->sub(DateInterval::createFromDateString("{$months} months"));
    }

    /**
     * Returns a new Time instance with $hours subtracted from the time.
     *
     * @return static
     */
    public function subYears(int $years)
    {
        $time = clone $this;

        return $time->sub(DateInterval::createFromDateString("{$years} years"));
    }

    //--------------------------------------------------------------------
    // Formatters
    //--------------------------------------------------------------------

    /**
     * Returns the localized value of the date in the format 'Y-m-d H:i:s'
     *
     * @throws Exception
     */
    public function toDateTimeString()
    {
        return $this->toLocalizedString('yyyy-MM-dd HH:mm:ss');
    }

    /**
     * Returns a localized version of the date in Y-m-d format.
     *
     * @throws Exception
     *
     * @return string
     */
    public function toDateString()
    {
        return $this->toLocalizedString('yyyy-MM-dd');
    }

    /**
     * Returns a localized version of the date in nicer date format:
     *
     *  i.e. Apr 1, 2017
     *
     * @throws Exception
     *
     * @return string
     */
    public function toFormattedDateString()
    {
        return $this->toLocalizedString('MMM d, yyyy');
    }

    /**
     * Returns a localized version of the time in nicer date format:
     *
     *  i.e. 13:20:33
     *
     * @throws Exception
     *
     * @return string
     */
    public function toTimeString()
    {
        return $this->toLocalizedString('HH:mm:ss');
    }

    /**
     * Returns the localized value of this instance in $format.
     *
     * @throws Exception
     *
     * @return bool|string
     */
    public function toLocalizedString(?string $format = null)
    {
        $format = $format ?? $this->toStringFormat;

        return IntlDateFormatter::formatObject($this->toDateTime(), $format, $this->locale);
    }

    //--------------------------------------------------------------------
    // Comparison
    //--------------------------------------------------------------------

    /**
     * Determines if the datetime passed in is equal to the current instance.
     * Equal in this case means that they represent the same moment in time,
     * and are not required to be in the same timezone, as both times are
     * converted to UTC and compared that way.
     *
     * @param DateTimeInterface|string|Time $testTime
     *
     * @throws Exception
     */
    public function equals($testTime, ?string $timezone = null): bool
    {
        $testTime = $this->getUTCObject($testTime, $timezone);

        $ourTime = $this->toDateTime()
            ->setTimezone(new DateTimeZone('UTC'))
            ->format('Y-m-d H:i:s');

        return $testTime->format('Y-m-d H:i:s') === $ourTime;
    }

    /**
     * Ensures that the times are identical, taking timezone into account.
     *
     * @param DateTimeInterface|string|Time $testTime
     *
     * @throws Exception
     */
    public function sameAs($testTime, ?string $timezone = null): bool
    {
        if ($testTime instanceof DateTimeInterface) {
            $testTime = $testTime->format('Y-m-d H:i:s');
        } elseif (is_string($testTime)) {
            $timezone = $timezone ?: $this->timezone;
            $timezone = $timezone instanceof DateTimeZone ? $timezone : new DateTimeZone($timezone);
            $testTime = new DateTime($testTime, $timezone);
            $testTime = $testTime->format('Y-m-d H:i:s');
        }

        $ourTime = $this->toDateTimeString();

        return $testTime === $ourTime;
    }

    /**
     * Determines if the current instance's time is before $testTime,
     * after converting to UTC.
     *
     * @param mixed $testTime
     *
     * @throws Exception
     */
    public function isBefore($testTime, ?string $timezone = null): bool
    {
        $testTime = $this->getUTCObject($testTime, $timezone)->getTimestamp();
        $ourTime  = $this->getTimestamp();

        return $ourTime < $testTime;
    }

    /**
     * Determines if the current instance's time is after $testTime,
     * after converting in UTC.
     *
     * @param mixed $testTime
     *
     * @throws Exception
     */
    public function isAfter($testTime, ?string $timezone = null): bool
    {
        $testTime = $this->getUTCObject($testTime, $timezone)->getTimestamp();
        $ourTime  = $this->getTimestamp();

        return $ourTime > $testTime;
    }

    //--------------------------------------------------------------------
    // Differences
    //--------------------------------------------------------------------

    /**
     * Returns a text string that is easily readable that describes
     * how long ago, or how long from now, a date is, like:
     *
     *  - 3 weeks ago
     *  - in 4 days
     *  - 6 hours ago
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function humanize()
    {
        $now  = IntlCalendar::fromDateTime(self::now($this->timezone)->toDateTimeString());
        $time = $this->getCalendar()->getTime();

        $years   = $now->fieldDifference($time, IntlCalendar::FIELD_YEAR);
        $months  = $now->fieldDifference($time, IntlCalendar::FIELD_MONTH);
        $days    = $now->fieldDifference($time, IntlCalendar::FIELD_DAY_OF_YEAR);
        $hours   = $now->fieldDifference($time, IntlCalendar::FIELD_HOUR_OF_DAY);
        $minutes = $now->fieldDifference($time, IntlCalendar::FIELD_MINUTE);

        $phrase = null;

        if ($years !== 0) {
            $phrase = lang('Time.years', [abs($years)]);
            $before = $years < 0;
        } elseif ($months !== 0) {
            $phrase = lang('Time.months', [abs($months)]);
            $before = $months < 0;
        } elseif ($days !== 0 && (abs($days) >= 7)) {
            $weeks  = ceil($days / 7);
            $phrase = lang('Time.weeks', [abs($weeks)]);
            $before = $days < 0;
        } elseif ($days !== 0) {
            $before = $days < 0;

            // Yesterday/Tomorrow special cases
            if (abs($days) === 1) {
                return $before ? lang('Time.yesterday') : lang('Time.tomorrow');
            }

            $phrase = lang('Time.days', [abs($days)]);
        } elseif ($hours !== 0) {
            $phrase = lang('Time.hours', [abs($hours)]);
            $before = $hours < 0;
        } elseif ($minutes !== 0) {
            $phrase = lang('Time.minutes', [abs($minutes)]);
            $before = $minutes < 0;
        } else {
            return lang('Time.now');
        }

        return $before ? lang('Time.ago', [$phrase]) : lang('Time.inFuture', [$phrase]);
    }

    /**
     * @param mixed $testTime
     *
     * @throws Exception
     *
     * @return TimeDifference
     */
    public function difference($testTime, ?string $timezone = null)
    {
        $testTime = $this->getUTCObject($testTime, $timezone);
        $ourTime  = $this->getUTCObject($this);

        return new TimeDifference($ourTime, $testTime);
    }

    //--------------------------------------------------------------------
    // Utilities
    //--------------------------------------------------------------------

    /**
     * Returns a Time instance with the timezone converted to UTC.
     *
     * @param mixed $time
     *
     * @throws Exception
     *
     * @return DateTime|static
     */
    public function getUTCObject($time, ?string $timezone = null)
    {
        if ($time instanceof self) {
            $time = $time->toDateTime();
        } elseif (is_string($time)) {
            $timezone = $timezone ?: $this->timezone;
            $timezone = $timezone instanceof DateTimeZone ? $timezone : new DateTimeZone($timezone);
            $time     = new DateTime($time, $timezone);
        }

        if ($time instanceof DateTime || $time instanceof DateTimeImmutable) {
            $time = $time->setTimezone(new DateTimeZone('UTC'));
        }

        return $time;
    }

    /**
     * Returns the IntlCalendar object used for this object,
     * taking into account the locale, date, etc.
     *
     * Primarily used internally to provide the difference and comparison functions,
     * but available for public consumption if they need it.
     *
     * @throws Exception
     *
     * @return IntlCalendar
     */
    public function getCalendar()
    {
        return IntlCalendar::fromDateTime($this->toDateTimeString());
    }

    /**
     * Check a time string to see if it includes a relative date (like 'next Tuesday').
     */
    protected static function hasRelativeKeywords(string $time): bool
    {
        // skip common format with a '-' in it
        if (preg_match('/\d{4}-\d{1,2}-\d{1,2}/', $time) !== 1) {
            return preg_match(static::$relativePattern, $time) > 0;
        }

        return false;
    }

    /**
     * Outputs a short format version of the datetime.
     *
     * @throws Exception
     */
    public function __toString(): string
    {
        return IntlDateFormatter::formatObject($this->toDateTime(), $this->toStringFormat, $this->locale);
    }

    /**
     * Allow for property-type access to any getX method...
     *
     * Note that we cannot use this for any of our setX methods,
     * as they return new Time objects, but the __set ignores
     * return values.
     * See http://php.net/manual/en/language.oop5.overloading.php
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);

        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        return null;
    }

    /**
     * Allow for property-type checking to any getX method...
     *
     * @param string $name
     */
    public function __isset($name): bool
    {
        $method = 'get' . ucfirst($name);

        return method_exists($this, $method);
    }

    /**
     * This is called when we unserialize the Time object.
     */
    public function __wakeup(): void
    {
        /**
         * Prior to unserialization, this is a string.
         *
         * @var string $timezone
         */
        $timezone = $this->timezone;

        $this->timezone = new DateTimeZone($timezone);
        parent::__construct($this->date, $this->timezone);
    }
}
