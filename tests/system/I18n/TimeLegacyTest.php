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

use CodeIgniter\Config\Factories;
use CodeIgniter\I18n\Exceptions\I18nException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use DateTime;
use DateTimeZone;
use IntlDateFormatter;
use Locale;

/**
 * @internal
 *
 * @group Others
 */
final class TimeLegacyTest extends CIUnitTestCase
{
    private string $currentLocale;

    protected function setUp(): void
    {
        parent::setUp();

        // Need to reset Services::language() that lang() uses in Time::humanize()
        $this->resetServices();

        helper('date');

        $this->currentLocale = Locale::getDefault();
        Locale::setDefault('en_US');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Locale::setDefault($this->currentLocale);

        // Reset current time.
        TimeLegacy::setTestNow();
    }

    public function testNewTimeNow()
    {
        $formatter = new IntlDateFormatter(
            'en_US',
            IntlDateFormatter::SHORT,
            IntlDateFormatter::SHORT,
            'America/Chicago', // Default for CodeIgniter
            IntlDateFormatter::GREGORIAN,
            'yyyy-MM-dd HH:mm:ss'
        );

        $time = new TimeLegacy('', 'America/Chicago');
        $this->assertSame($formatter->format($time), $time->toDateTimeString());
    }

    public function testTimeWithTimezone()
    {
        $formatter = new IntlDateFormatter(
            'en_US',
            IntlDateFormatter::SHORT,
            IntlDateFormatter::SHORT,
            'Europe/London', // Default for CodeIgniter
            IntlDateFormatter::GREGORIAN,
            'yyyy-MM-dd HH:mm:ss'
        );

        $time = new TimeLegacy('now', 'Europe/London');

        $this->assertSame($formatter->format($time), $time->toDateTimeString());
    }

    public function testTimeWithTimezoneAndLocale()
    {
        $formatter = new IntlDateFormatter(
            'fr_FR',
            IntlDateFormatter::SHORT,
            IntlDateFormatter::SHORT,
            'Europe/London', // Default for CodeIgniter
            IntlDateFormatter::GREGORIAN,
            'yyyy-MM-dd HH:mm:ss'
        );

        $time = new TimeLegacy('now', 'Europe/London', 'fr_FR');

        $this->assertSame($formatter->format($time), $time->toDateTimeString());
    }

    public function testTimeWithDateTimeZone()
    {
        $formatter = new IntlDateFormatter(
            'fr_FR',
            IntlDateFormatter::SHORT,
            IntlDateFormatter::SHORT,
            'Europe/London',
            IntlDateFormatter::GREGORIAN,
            'yyyy-MM-dd HH:mm:ss'
        );

        $time = new TimeLegacy('now', new DateTimeZone('Europe/London'), 'fr_FR');

        $this->assertSame($formatter->format($time), $time->toDateTimeString());
    }

    public function testToDateTime()
    {
        $time = new TimeLegacy();

        $obj = $time->toDateTime();

        $this->assertInstanceOf(DateTime::class, $obj);
    }

    public function testNow()
    {
        $time  = TimeLegacy::now();
        $time1 = new DateTime();

        $this->assertInstanceOf(TimeLegacy::class, $time);
        $this->assertSame($time->getTimestamp(), $time1->getTimestamp());
    }

    public function testParse()
    {
        $time  = TimeLegacy::parse('next Tuesday', 'America/Chicago');
        $time1 = new DateTime('now', new DateTimeZone('America/Chicago'));
        $time1->modify('next Tuesday');

        $this->assertSame($time->getTimestamp(), $time1->getTimestamp());
    }

    public function testToDateTimeString()
    {
        $time = TimeLegacy::parse('2017-01-12 00:00', 'America/Chicago');

        $this->assertSame('2017-01-12 00:00:00', $time->toDateTimeString());
    }

    public function testToDateTimeStringWithTimeZone()
    {
        $time = TimeLegacy::parse('2017-01-12 00:00', 'Europe/London');

        $expects = new DateTime('2017-01-12', new DateTimeZone('Europe/London'));

        $this->assertSame($expects->format('Y-m-d H:i:s'), $time->toDateTimeString());
    }

    public function testToday()
    {
        $time = TimeLegacy::today();

        $this->assertSame(date('Y-m-d 00:00:00'), $time->toDateTimeString());
    }

    public function testTodayLocalized()
    {
        $time = TimeLegacy::today('Europe/London');

        $this->assertSame(date('Y-m-d 00:00:00'), $time->toDateTimeString());
    }

    public function testYesterday()
    {
        $time = TimeLegacy::yesterday();

        $this->assertSame(date('Y-m-d 00:00:00', strtotime('-1 day')), $time->toDateTimeString());
    }

    public function testTomorrow()
    {
        $time = TimeLegacy::tomorrow();

        $this->assertSame(date('Y-m-d 00:00:00', strtotime('+1 day')), $time->toDateTimeString());
    }

    public function testCreateFromDate()
    {
        $time = TimeLegacy::createFromDate(2017, 03, 05, 'America/Chicago');

        $this->assertSame(date('Y-m-d 00:00:00', strtotime('2017-03-05 00:00:00')), $time->toDateTimeString());
    }

    public function testCreateFromDateLocalized()
    {
        $time = TimeLegacy::createFromDate(2017, 03, 05, 'Europe/London');

        $this->assertSame(date('Y-m-d 00:00:00', strtotime('2017-03-05 00:00:00')), $time->toDateTimeString());
    }

    public function testCreateFromTime()
    {
        $time = TimeLegacy::createFromTime(10, 03, 05, 'America/Chicago');

        $this->assertSame(date('Y-m-d 10:03:05'), $time->toDateTimeString());
    }

    public function testCreateFromTimeEvening()
    {
        $time = TimeLegacy::createFromTime(20, 03, 05, 'America/Chicago');

        $this->assertSame(date('Y-m-d 20:03:05'), $time->toDateTimeString());
    }

    public function testCreateFromTimeLocalized()
    {
        $time = TimeLegacy::createFromTime(10, 03, 05, 'Europe/London');

        $this->assertCloseEnoughString(date('Y-m-d 10:03:05'), $time->toDateTimeString());
    }

    public function testCreateFromFormat()
    {
        $now = new DateTime('now');

        TimeLegacy::setTestNow($now);
        $time = TimeLegacy::createFromFormat('F j, Y', 'January 15, 2017', 'America/Chicago');

        $this->assertCloseEnoughString(date('2017-01-15 H:i:s', $now->getTimestamp()), $time->toDateTimeString());
        TimeLegacy::setTestNow();
    }

    public function testCreateFromFormatWithTimezoneString()
    {
        $time = TimeLegacy::createFromFormat('F j, Y', 'January 15, 2017', 'Europe/London');

        $this->assertCloseEnoughString(date('2017-01-15 H:i:s'), $time->toDateTimeString());
    }

    public function testCreateFromFormatWithTimezoneObject()
    {
        $tz = new DateTimeZone('Europe/London');

        $time = TimeLegacy::createFromFormat('F j, Y', 'January 15, 2017', $tz);

        $this->assertCloseEnoughString(date('2017-01-15 H:i:s'), $time->toDateTimeString());
    }

    public function testCreateFromFormatWithInvalidFormat()
    {
        $format = 'foobar';

        $this->expectException(I18nException::class);
        $this->expectExceptionMessage(lang('Time.invalidFormat', [$format]));

        TimeLegacy::createFromFormat($format, 'America/Chicago');
    }

    public function testCreateFromTimestamp()
    {
        // Set the timezone temporarily to UTC to make sure the test timestamp is correct
        $tz = date_default_timezone_get();
        date_default_timezone_set('UTC');

        $timestamp = strtotime('2017-03-18 midnight');

        date_default_timezone_set($tz);

        $time = TimeLegacy::createFromTimestamp($timestamp);

        $this->assertSame(date('2017-03-18 00:00:00'), $time->toDateTimeString());
    }

    public function testCreateFromTimestampWithTimezone()
    {
        // Set the timezone temporarily to UTC to make sure the test timestamp is correct
        $tz = date_default_timezone_get();
        date_default_timezone_set('UTC');

        $timestamp = strtotime('2017-03-18 midnight'); // in UTC

        date_default_timezone_set($tz);

        $time = TimeLegacy::createFromTimestamp($timestamp, 'Asia/Jakarta'); // UTC +7

        $this->assertSame(date('2017-03-18 07:00:00'), $time->toDateTimeString());
    }

    public function testTestNow()
    {
        $this->assertFalse(TimeLegacy::hasTestNow());
        $this->assertCloseEnoughString(date('Y-m-d H:i:s', time()), TimeLegacy::now()->toDateTimeString());

        $t = new TimeLegacy('2000-01-02');
        TimeLegacy::setTestNow($t);

        $this->assertTrue(TimeLegacy::hasTestNow());
        $this->assertSame('2000-01-02 00:00:00', TimeLegacy::now()->toDateTimeString());

        TimeLegacy::setTestNow();
        $this->assertCloseEnoughString(date('Y-m-d H:i:s', time()), TimeLegacy::now()->toDateTimeString());
    }

    public function testMagicIssetTrue()
    {
        $time = TimeLegacy::parse('January 1, 2016');

        $this->assertTrue(isset($time->year));
    }

    public function testMagicIssetFalse()
    {
        $time = TimeLegacy::parse('January 1, 2016');

        $this->assertFalse(isset($time->foobar));
    }

    public function testGetYear()
    {
        $time  = TimeLegacy::parse('January 1, 2016');
        $time2 = TimeLegacy::parse('December 31, 2019');

        $this->assertSame('2016', $time->year);
        $this->assertSame('2019', $time2->year);
    }

    public function testGetMonth()
    {
        $time = TimeLegacy::parse('August 1, 2016');

        $this->assertSame('8', $time->month);
    }

    public function testGetDay()
    {
        $time = TimeLegacy::parse('August 12, 2016');

        $this->assertSame('12', $time->day);
    }

    public function testGetHour()
    {
        $time = TimeLegacy::parse('August 12, 2016 4:15pm');

        $this->assertSame('16', $time->hour);
    }

    public function testGetMinute()
    {
        $time = TimeLegacy::parse('August 12, 2016 4:15pm');

        $this->assertSame('15', $time->minute);
    }

    public function testGetSecond()
    {
        $time = TimeLegacy::parse('August 12, 2016 4:15:23pm');

        $this->assertSame('23', $time->second);
    }

    public function testGetDayOfWeek()
    {
        $time = TimeLegacy::parse('August 12, 2016 4:15:23pm');

        $this->assertSame('6', $time->dayOfWeek);
    }

    public function testGetDayOfYear()
    {
        $time = TimeLegacy::parse('August 12, 2016 4:15:23pm');

        $this->assertSame('225', $time->dayOfYear);
    }

    public function testGetWeekOfMonth()
    {
        $time = TimeLegacy::parse('August 12, 2016 4:15:23pm');

        $this->assertSame('2', $time->weekOfMonth);
    }

    public function testGetWeekOfYear()
    {
        $time = TimeLegacy::parse('August 12, 2016 4:15:23pm');

        $this->assertSame('33', $time->weekOfYear);
    }

    public function testGetTimestamp()
    {
        $time     = TimeLegacy::parse('August 12, 2016 4:15:23pm');
        $expected = strtotime('August 12, 2016 4:15:23pm');

        $this->assertSame($expected, $time->timestamp);
    }

    public function testGetAge()
    {
        $time = TimeLegacy::parse('5 years ago');

        $this->assertSame(5, $time->getAge());
        $this->assertSame(5, $time->age);
    }

    public function testAgeNow()
    {
        $time = new TimeLegacy();

        $this->assertSame(0, $time->getAge());
    }

    public function testAgeFuture()
    {
        TimeLegacy::setTestNow('June 20, 2022', 'America/Chicago');
        $time = TimeLegacy::parse('August 12, 2116 4:15:23pm');

        $this->assertSame(0, $time->getAge());
    }

    public function testGetAgeSameDayOfBirthday()
    {
        TimeLegacy::setTestNow('December 31, 2022', 'America/Chicago');
        $time = TimeLegacy::parse('December 31, 2020');

        $this->assertSame(2, $time->getAge());
    }

    public function testGetAgeNextDayOfBirthday()
    {
        TimeLegacy::setTestNow('January 1, 2022', 'America/Chicago');
        $time = TimeLegacy::parse('December 31, 2020');

        $this->assertSame(1, $time->getAge());
    }

    public function testGetAgeBeforeDayOfBirthday()
    {
        TimeLegacy::setTestNow('December 30, 2021', 'America/Chicago');
        $time = TimeLegacy::parse('December 31, 2020');

        $this->assertSame(0, $time->getAge());
    }

    public function testGetQuarter()
    {
        $time = TimeLegacy::parse('April 15, 2015');

        $this->assertSame('2', $time->quarter);
    }

    public function testGetDST()
    {
        // America/Chicago. DST from early March -> early Nov
        $time = TimeLegacy::createFromDate(2012, 1, 1, 'America/Chicago');
        $this->assertFalse($time->dst);

        $time = TimeLegacy::createFromDate(2012, 9, 1, 'America/Chicago');
        $this->assertTrue($time->dst);
    }

    public function testGetDSTUnobserved()
    {
        // Asia/Shanghai. DST not observed
        $tz   = new DateTimeZone('Asia/Shanghai');
        $time = TimeLegacy::createFromDate(2012, 1, 1, $tz, 'Asia/Shanghai');

        $this->assertFalse($time->dst);
    }

    public function testGetLocal()
    {
        $this->assertTrue(TimeLegacy::now()->local);
        $this->assertFalse(TimeLegacy::now('Europe/London')->local);
    }

    public function testGetUtc()
    {
        $this->assertFalse(TimeLegacy::now('America/Chicago')->utc);
        $this->assertTrue(TimeLegacy::now('UTC')->utc);
    }

    public function testGetTimezone()
    {
        $instance = TimeLegacy::now()->getTimezone();

        $this->assertInstanceOf(DateTimeZone::class, $instance);
    }

    public function testGetTimezonename()
    {
        $this->assertSame('America/Chicago', TimeLegacy::now('America/Chicago')->getTimezoneName());
        $this->assertSame('Europe/London', TimeLegacy::now('Europe/London')->timezoneName);
    }

    public function testSetYear()
    {
        $time  = TimeLegacy::parse('May 10, 2017');
        $time2 = $time->setYear(2015);

        $this->assertInstanceOf(TimeLegacy::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('2015-05-10 00:00:00', $time2->toDateTimeString());
    }

    public function testSetMonthNumber()
    {
        $time  = TimeLegacy::parse('May 10, 2017');
        $time2 = $time->setMonth(4);

        $this->assertInstanceOf(TimeLegacy::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('2017-04-10 00:00:00', $time2->toDateTimeString());
    }

    public function testSetMonthLongName()
    {
        $time  = TimeLegacy::parse('May 10, 2017');
        $time2 = $time->setMonth('April');

        $this->assertInstanceOf(TimeLegacy::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('2017-04-10 00:00:00', $time2->toDateTimeString());
    }

    public function testSetMonthShortName()
    {
        $time  = TimeLegacy::parse('May 10, 2017');
        $time2 = $time->setMonth('Feb');

        $this->assertInstanceOf(TimeLegacy::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('2017-02-10 00:00:00', $time2->toDateTimeString());
    }

    public function testSetDay()
    {
        $time  = TimeLegacy::parse('May 10, 2017');
        $time2 = $time->setDay(15);

        $this->assertInstanceOf(TimeLegacy::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('2017-05-15 00:00:00', $time2->toDateTimeString());
    }

    public function testSetDayOverMaxInCurrentMonth()
    {
        $this->expectException(I18nException::class);

        $time = TimeLegacy::parse('Feb 02, 2009');
        $time->setDay(29);
    }

    public function testSetDayNotOverMaxInCurrentMonth()
    {
        $time  = TimeLegacy::parse('Feb 02, 2012');
        $time2 = $time->setDay(29);

        $this->assertInstanceOf(TimeLegacy::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('2012-02-29 00:00:00', $time2->toDateTimeString());
    }

    public function testSetHour()
    {
        $time  = TimeLegacy::parse('May 10, 2017');
        $time2 = $time->setHour(15);

        $this->assertInstanceOf(TimeLegacy::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('2017-05-10 15:00:00', $time2->toDateTimeString());
    }

    public function testSetMinute()
    {
        $time  = TimeLegacy::parse('May 10, 2017');
        $time2 = $time->setMinute(30);

        $this->assertInstanceOf(TimeLegacy::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('2017-05-10 00:30:00', $time2->toDateTimeString());
    }

    public function testSetSecond()
    {
        $time  = TimeLegacy::parse('May 10, 2017');
        $time2 = $time->setSecond(20);

        $this->assertInstanceOf(TimeLegacy::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('2017-05-10 00:00:20', $time2->toDateTimeString());
    }

    public function testSetMonthTooSmall()
    {
        $this->expectException(I18nException::class);

        $time = TimeLegacy::parse('May 10, 2017');
        $time->setMonth(-5);
    }

    public function testSetMonthTooBig()
    {
        $this->expectException(I18nException::class);

        $time = TimeLegacy::parse('May 10, 2017');
        $time->setMonth(30);
    }

    public function testSetDayTooSmall()
    {
        $this->expectException(I18nException::class);

        $time = TimeLegacy::parse('May 10, 2017');
        $time->setDay(-5);
    }

    public function testSetDayTooBig()
    {
        $this->expectException(I18nException::class);

        $time = TimeLegacy::parse('May 10, 2017');
        $time->setDay(80);
    }

    public function testSetHourTooSmall()
    {
        $this->expectException(I18nException::class);

        $time = TimeLegacy::parse('May 10, 2017');
        $time->setHour(-5);
    }

    public function testSetHourTooBig()
    {
        $this->expectException(I18nException::class);

        $time = TimeLegacy::parse('May 10, 2017');
        $time->setHour(80);
    }

    public function testSetMinuteTooSmall()
    {
        $this->expectException(I18nException::class);

        $time = TimeLegacy::parse('May 10, 2017');
        $time->setMinute(-5);
    }

    public function testSetMinuteTooBig()
    {
        $this->expectException(I18nException::class);

        $time = TimeLegacy::parse('May 10, 2017');
        $time->setMinute(80);
    }

    public function testSetSecondTooSmall()
    {
        $this->expectException(I18nException::class);

        $time = TimeLegacy::parse('May 10, 2017');
        $time->setSecond(-5);
    }

    public function testSetSecondTooBig()
    {
        $this->expectException(I18nException::class);

        $time = TimeLegacy::parse('May 10, 2017');
        $time->setSecond(80);
    }

    public function testSetTimezone()
    {
        $time  = TimeLegacy::parse('May 10, 2017', 'America/Chicago');
        $time2 = $time->setTimezone('Europe/London');

        $this->assertInstanceOf(TimeLegacy::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('America/Chicago', $time->getTimezoneName());
        $this->assertSame('Europe/London', $time2->getTimezoneName());
    }

    public function testSetTimestamp()
    {
        $time  = TimeLegacy::parse('May 10, 2017', 'America/Chicago');
        $stamp = strtotime('April 1, 2017');
        $time2 = $time->setTimestamp($stamp);

        $this->assertInstanceOf(TimeLegacy::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('2017-04-01 00:00:00', $time2->toDateTimeString());
    }

    public function testToDateString()
    {
        $time = TimeLegacy::parse('May 10, 2017', 'America/Chicago');
        $this->assertSame('2017-05-10', $time->toDateString());
    }

    public function testToFormattedDateString()
    {
        $time = TimeLegacy::parse('2017-05-10', 'America/Chicago');
        $this->assertSame('May 10, 2017', $time->toFormattedDateString());
    }

    public function testToTimeString()
    {
        $time = TimeLegacy::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $this->assertSame('13:20:33', $time->toTimeString());
    }

    // Add/Subtract

    public function testCanAddSeconds()
    {
        $time    = TimeLegacy::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->addSeconds(10);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2017-01-10 13:20:43', $newTime->toDateTimeString());
    }

    public function testCanAddMinutes()
    {
        $time    = TimeLegacy::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->addMinutes(10);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2017-01-10 13:30:33', $newTime->toDateTimeString());
    }

    public function testCanAddHours()
    {
        $time    = TimeLegacy::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->addHours(3);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2017-01-10 16:20:33', $newTime->toDateTimeString());
    }

    public function testCanAddDays()
    {
        $time    = TimeLegacy::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->addDays(3);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2017-01-13 13:20:33', $newTime->toDateTimeString());
    }

    public function testCanAddMonths()
    {
        $time    = TimeLegacy::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->addMonths(3);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2017-04-10 13:20:33', $newTime->toDateTimeString());
    }

    public function testCanAddMonthsOverYearBoundary()
    {
        $time    = TimeLegacy::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->addMonths(13);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2018-02-10 13:20:33', $newTime->toDateTimeString());
    }

    public function testCanAddYears()
    {
        $time    = TimeLegacy::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->addYears(3);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2020-01-10 13:20:33', $newTime->toDateTimeString());
    }

    public function testCanSubtractSeconds()
    {
        $time    = TimeLegacy::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->subSeconds(10);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2017-01-10 13:20:23', $newTime->toDateTimeString());
    }

    public function testCanSubtractMinutes()
    {
        $time    = TimeLegacy::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->subMinutes(10);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2017-01-10 13:10:33', $newTime->toDateTimeString());
    }

    public function testCanSubtractHours()
    {
        $time    = TimeLegacy::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->subHours(3);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2017-01-10 10:20:33', $newTime->toDateTimeString());
    }

    public function testCanSubtractDays()
    {
        $time    = TimeLegacy::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->subDays(3);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2017-01-07 13:20:33', $newTime->toDateTimeString());
    }

    public function testCanSubtractMonths()
    {
        $time    = TimeLegacy::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->subMonths(3);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2016-10-10 13:20:33', $newTime->toDateTimeString());
    }

    public function testCanSubtractYears()
    {
        $time    = TimeLegacy::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->subYears(3);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2014-01-10 13:20:33', $newTime->toDateTimeString());
    }

    // Comparison

    public function testEqualWithDifferent()
    {
        $time1 = TimeLegacy::parse('January 10, 2017 21:50:00', 'America/Chicago');
        $time2 = TimeLegacy::parse('January 11, 2017 03:50:00', 'Europe/London');

        $this->assertTrue($time1->equals($time2));
    }

    public function testEqualWithSame()
    {
        $time1 = TimeLegacy::parse('January 10, 2017 21:50:00', 'America/Chicago');
        $time2 = TimeLegacy::parse('January 10, 2017 21:50:00', 'America/Chicago');

        $this->assertTrue($time1->equals($time2));
    }

    public function testEqualWithDateTime()
    {
        $time1 = TimeLegacy::parse('January 10, 2017 21:50:00', 'America/Chicago');
        $time2 = new DateTime('January 11, 2017 03:50:00', new DateTimeZone('Europe/London'));

        $this->assertTrue($time1->equals($time2));
    }

    public function testEqualWithSameDateTime()
    {
        $time1 = TimeLegacy::parse('January 10, 2017 21:50:00', 'America/Chicago');
        $time2 = new DateTime('January 10, 2017 21:50:00', new DateTimeZone('America/Chicago'));

        $this->assertTrue($time1->equals($time2));
    }

    public function testEqualWithString()
    {
        $time1 = TimeLegacy::parse('January 10, 2017 21:50:00', 'America/Chicago');

        $this->assertTrue($time1->equals('January 11, 2017 03:50:00', 'Europe/London'));
    }

    public function testEqualWithStringAndNotimezone()
    {
        $time1 = TimeLegacy::parse('January 10, 2017 21:50:00', 'America/Chicago');

        $this->assertTrue($time1->equals('January 10, 2017 21:50:00'));
    }

    public function testSameSuccess()
    {
        $time1 = TimeLegacy::parse('January 10, 2017 21:50:00', 'America/Chicago');
        $time2 = TimeLegacy::parse('January 10, 2017 21:50:00', 'America/Chicago');

        $this->assertTrue($time1->sameAs($time2));
    }

    public function testSameFailure()
    {
        $time1 = TimeLegacy::parse('January 10, 2017 21:50:00', 'America/Chicago');
        $time2 = TimeLegacy::parse('January 11, 2017 03:50:00', 'Europe/London');

        $this->assertFalse($time1->sameAs($time2));
    }

    public function testSameSuccessAsString()
    {
        $time1 = TimeLegacy::parse('January 10, 2017 21:50:00', 'America/Chicago');

        $this->assertTrue($time1->sameAs('January 10, 2017 21:50:00', 'America/Chicago'));
    }

    public function testSameFailAsString()
    {
        $time1 = TimeLegacy::parse('January 10, 2017 21:50:00', 'America/Chicago');

        $this->assertFalse($time1->sameAs('January 11, 2017 03:50:00', 'Europe/London'));
    }

    public function testBefore()
    {
        $time1 = TimeLegacy::parse('January 10, 2017 21:50:00', 'America/Chicago');
        $time2 = TimeLegacy::parse('January 11, 2017 03:50:00', 'America/Chicago');

        $this->assertTrue($time1->isBefore($time2));
        $this->assertFalse($time2->isBefore($time1));
    }

    public function testAfter()
    {
        $time1 = TimeLegacy::parse('January 10, 2017 21:50:00', 'America/Chicago');
        $time2 = TimeLegacy::parse('January 11, 2017 03:50:00', 'America/Chicago');

        $this->assertFalse($time1->isAfter($time2));
        $this->assertTrue($time2->isAfter($time1));
    }

    public function testHumanizeYearsSingle()
    {
        TimeLegacy::setTestNow('March 10, 2017', 'America/Chicago');
        $time = TimeLegacy::parse('March 9, 2016 12:00:00', 'America/Chicago');

        $this->assertSame('1 year ago', $time->humanize());
    }

    public function testHumanizeYearsPlural()
    {
        TimeLegacy::setTestNow('March 10, 2017', 'America/Chicago');
        $time = TimeLegacy::parse('March 9, 2014 12:00:00', 'America/Chicago');

        $this->assertSame('3 years ago', $time->humanize());
    }

    public function testHumanizeYearsForward()
    {
        TimeLegacy::setTestNow('January 1, 2017', 'America/Chicago');
        $time = TimeLegacy::parse('January 1, 2018 12:00:00', 'America/Chicago');

        $this->assertSame('in 1 year', $time->humanize());
    }

    public function testHumanizeMonthsSingle()
    {
        TimeLegacy::setTestNow('March 10, 2017', 'America/Chicago');
        $time = TimeLegacy::parse('February 9, 2017', 'America/Chicago');

        $this->assertSame('1 month ago', $time->humanize());
    }

    public function testHumanizeMonthsPlural()
    {
        TimeLegacy::setTestNow('March 1, 2017', 'America/Chicago');
        $time = TimeLegacy::parse('January 1, 2017', 'America/Chicago');

        $this->assertSame('2 months ago', $time->humanize());
    }

    public function testHumanizeMonthsForward()
    {
        TimeLegacy::setTestNow('March 1, 2017', 'America/Chicago');
        $time = TimeLegacy::parse('April 1, 2017', 'America/Chicago');

        $this->assertSame('in 1 month', $time->humanize());
    }

    public function testHumanizeDaysSingle()
    {
        TimeLegacy::setTestNow('March 10, 2017', 'America/Chicago');
        $time = TimeLegacy::parse('March 8, 2017', 'America/Chicago');

        $this->assertSame('2 days ago', $time->humanize());
    }

    public function testHumanizeDaysPlural()
    {
        TimeLegacy::setTestNow('March 10, 2017', 'America/Chicago');
        $time = TimeLegacy::parse('March 8, 2017', 'America/Chicago');

        $this->assertSame('2 days ago', $time->humanize());
    }

    public function testHumanizeDaysForward()
    {
        TimeLegacy::setTestNow('March 10, 2017', 'America/Chicago');
        $time = TimeLegacy::parse('March 12, 2017', 'America/Chicago');

        $this->assertSame('in 2 days', $time->humanize());
    }

    public function testHumanizeDaysTomorrow()
    {
        TimeLegacy::setTestNow('March 10, 2017', 'America/Chicago');
        $time = TimeLegacy::parse('March 11, 2017', 'America/Chicago');

        $this->assertSame('Tomorrow', $time->humanize());
    }

    public function testHumanizeDaysYesterday()
    {
        TimeLegacy::setTestNow('March 10, 2017', 'America/Chicago');
        $time = TimeLegacy::parse('March 9, 2017', 'America/Chicago');

        $this->assertSame('Yesterday', $time->humanize());
    }

    public function testHumanizeHoursAsTime()
    {
        TimeLegacy::setTestNow('March 10, 2017 12:00', 'America/Chicago');
        $time = TimeLegacy::parse('March 10, 2017 14:00', 'America/Chicago');

        $this->assertSame('in 2 hours', $time->humanize());
    }

    public function testHumanizeHoursAWhileAgo()
    {
        TimeLegacy::setTestNow('March 10, 2017 12:00', 'America/Chicago');
        $time = TimeLegacy::parse('March 10, 2017 8:00', 'America/Chicago');

        $this->assertSame('4 hours ago', $time->humanize());
    }

    public function testHumanizeMinutesSingle()
    {
        TimeLegacy::setTestNow('March 10, 2017 12:30', 'America/Chicago');
        $time = TimeLegacy::parse('March 10, 2017 12:29', 'America/Chicago');

        $this->assertSame('1 minute ago', $time->humanize());
    }

    public function testHumanizeMinutesPlural()
    {
        TimeLegacy::setTestNow('March 10, 2017 12:30', 'America/Chicago');
        $time = TimeLegacy::parse('March 10, 2017 12:28', 'America/Chicago');

        $this->assertSame('2 minutes ago', $time->humanize());
    }

    public function testHumanizeMinutesForward()
    {
        TimeLegacy::setTestNow('March 10, 2017 12:30', 'America/Chicago');
        $time = TimeLegacy::parse('March 10, 2017 12:31', 'America/Chicago');

        $this->assertSame('in 1 minute', $time->humanize());
    }

    public function testHumanizeWeeksSingle()
    {
        TimeLegacy::setTestNow('March 10, 2017', 'America/Chicago');
        $time = TimeLegacy::parse('March 2, 2017', 'America/Chicago');

        $this->assertSame('1 week ago', $time->humanize());
    }

    public function testHumanizeWeeksPlural()
    {
        TimeLegacy::setTestNow('March 30, 2017', 'America/Chicago');
        $time = TimeLegacy::parse('March 15, 2017', 'America/Chicago');

        $this->assertSame('2 weeks ago', $time->humanize());
    }

    public function testHumanizeWeeksForward()
    {
        TimeLegacy::setTestNow('March 10, 2017', 'America/Chicago');
        $time = TimeLegacy::parse('March 18, 2017', 'America/Chicago');

        $this->assertSame('in 2 weeks', $time->humanize());
    }

    public function testHumanizeNow()
    {
        TimeLegacy::setTestNow('March 10, 2017', 'America/Chicago');
        $time = TimeLegacy::parse('March 10, 2017', 'America/Chicago');

        $this->assertSame('Just now', $time->humanize());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4708
     */
    public function testHumanizeWithArLocale()
    {
        $this->resetServices();

        $currentLocale = Locale::getDefault();
        Locale::setDefault('ar');

        $config                   = new App();
        $config->supportedLocales = ['ar'];
        $config->defaultLocale    = 'ar';
        Factories::injectMock('config', 'App', $config);

        TimeLegacy::setTestNow('2022-06-14 12:00', 'America/Chicago');

        $date = '2022-06-07 12:00';
        $time = TimeLegacy::parse($date, 'America/Chicago');

        $this->assertSame('١ week ago', $time->humanize());

        Locale::setDefault($currentLocale);
    }

    public function testSetTimezoneDate()
    {
        $time  = TimeLegacy::parse('13 May 2020 10:00', 'GMT');
        $time2 = $time->setTimezone('GMT+8');
        $this->assertSame('2020-05-13 10:00:00', $time->toDateTimeString());
        $this->assertSame('2020-05-13 18:00:00', $time2->toDateTimeString());
    }

    public function testCreateFromInstance()
    {
        $datetime = new DateTime();
        $time     = TimeLegacy::createFromInstance($datetime);
        $this->assertInstanceOf(TimeLegacy::class, $time);
        $this->assertTrue($time->sameAs($datetime));
    }

    public function testGetter()
    {
        $time = TimeLegacy::parse('August 12, 2016 4:15:23pm');

        $this->assertNull($time->weekOfWeek);
    }

    // @TODO Uncomment when PHP 8.2.4 Segmentation fault fixed.
    //    public function testUnserializeTimeObject()
    //    {
    //        $time1     = new TimeLegacy('August 28, 2020 10:04:00pm', 'Asia/Manila', 'en');
    //        $timeCache = serialize($time1);
    //        $time2     = unserialize($timeCache);
    //
    //        $this->assertInstanceOf(TimeLegacy::class, $time2);
    //        $this->assertTrue($time2->equals($time1));
    //        $this->assertNotSame($time1, $time2);
    //    }

    public function testSetTestNowWithFaLocale()
    {
        Locale::setDefault('fa');

        TimeLegacy::setTestNow('2017/03/10 12:00', 'Asia/Tokyo');

        $now = TimeLegacy::now()->format('c');

        $this->assertSame('2017-03-10T12:00:00+09:00', $now);
    }

    /**
     * @dataProvider provideLocales
     */
    public function testToStringDoesNotDependOnLocale(string $locale)
    {
        Locale::setDefault($locale);

        $time = new TimeLegacy('2017/03/10 12:00');

        $this->assertSame('2017-03-10 12:00:00', (string) $time);
    }

    public function provideLocales(): iterable
    {
        yield from [
            ['en'],
            ['de'],
            ['ar'],
            ['fa'],
        ];
    }

    public function testModify()
    {
        $time  = new TimeLegacy('2017/03/10 12:00');
        $time2 = $time->modify('+1 day');

        $this->assertInstanceOf(TimeLegacy::class, $time2);
        $this->assertSame($time, $time2); // mutable
        $this->assertSame('2017-03-11 12:00:00', (string) $time);
        $this->assertSame('2017-03-11 12:00:00', (string) $time2);
    }
}
