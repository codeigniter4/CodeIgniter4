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
final class TimeTest extends CIUnitTestCase
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
        Time::setTestNow();
    }

    public function testNewTimeNow(): void
    {
        $formatter = new IntlDateFormatter(
            'en_US',
            IntlDateFormatter::SHORT,
            IntlDateFormatter::SHORT,
            'America/Chicago', // Default for CodeIgniter
            IntlDateFormatter::GREGORIAN,
            'yyyy-MM-dd HH:mm:ss'
        );

        $time = new Time('', 'America/Chicago');
        $this->assertSame($formatter->format($time), $time->toDateTimeString());
    }

    public function testTimeWithTimezone(): void
    {
        $formatter = new IntlDateFormatter(
            'en_US',
            IntlDateFormatter::SHORT,
            IntlDateFormatter::SHORT,
            'Europe/London', // Default for CodeIgniter
            IntlDateFormatter::GREGORIAN,
            'yyyy-MM-dd HH:mm:ss'
        );

        $time = new Time('now', 'Europe/London');

        $this->assertSame($formatter->format($time), $time->toDateTimeString());
    }

    public function testTimeWithTimezoneAndLocale(): void
    {
        $formatter = new IntlDateFormatter(
            'fr_FR',
            IntlDateFormatter::SHORT,
            IntlDateFormatter::SHORT,
            'Europe/London', // Default for CodeIgniter
            IntlDateFormatter::GREGORIAN,
            'yyyy-MM-dd HH:mm:ss'
        );

        $time = new Time('now', 'Europe/London', 'fr_FR');

        $this->assertSame($formatter->format($time), $time->toDateTimeString());
    }

    public function testTimeWithDateTimeZone(): void
    {
        $formatter = new IntlDateFormatter(
            'fr_FR',
            IntlDateFormatter::SHORT,
            IntlDateFormatter::SHORT,
            'Europe/London',
            IntlDateFormatter::GREGORIAN,
            'yyyy-MM-dd HH:mm:ss'
        );

        $time = new Time('now', new DateTimeZone('Europe/London'), 'fr_FR');

        $this->assertSame($formatter->format($time), $time->toDateTimeString());
    }

    public function testToDateTime(): void
    {
        $time = new Time();

        $obj = $time->toDateTime();

        $this->assertInstanceOf(DateTime::class, $obj);
    }

    public function testNow(): void
    {
        $time  = Time::now();
        $time1 = new DateTime();

        $this->assertInstanceOf(Time::class, $time);
        $this->assertSame($time->getTimestamp(), $time1->getTimestamp());
    }

    public function testParse(): void
    {
        $time  = Time::parse('next Tuesday', 'America/Chicago');
        $time1 = new DateTime('now', new DateTimeZone('America/Chicago'));
        $time1->modify('next Tuesday');

        $this->assertSame($time->getTimestamp(), $time1->getTimestamp());
    }

    public function testToDateTimeString(): void
    {
        $time = Time::parse('2017-01-12 00:00', 'America/Chicago');

        $this->assertSame('2017-01-12 00:00:00', $time->toDateTimeString());
    }

    public function testToDateTimeStringWithTimeZone(): void
    {
        $time = Time::parse('2017-01-12 00:00', 'Europe/London');

        $expects = new DateTime('2017-01-12', new DateTimeZone('Europe/London'));

        $this->assertSame($expects->format('Y-m-d H:i:s'), $time->toDateTimeString());
    }

    public function testToday(): void
    {
        $time = Time::today();

        $this->assertSame(date('Y-m-d 00:00:00'), $time->toDateTimeString());
    }

    public function testTodayLocalized(): void
    {
        $time = Time::today('Europe/London');

        $this->assertSame(date('Y-m-d 00:00:00'), $time->toDateTimeString());
    }

    public function testYesterday(): void
    {
        $time = Time::yesterday();

        $this->assertSame(date('Y-m-d 00:00:00', strtotime('-1 day')), $time->toDateTimeString());
    }

    public function testTomorrow(): void
    {
        $time = Time::tomorrow();

        $this->assertSame(date('Y-m-d 00:00:00', strtotime('+1 day')), $time->toDateTimeString());
    }

    public function testCreateFromDate(): void
    {
        $time = Time::createFromDate(2017, 03, 05, 'America/Chicago');

        $this->assertSame(date('Y-m-d 00:00:00', strtotime('2017-03-05 00:00:00')), $time->toDateTimeString());
    }

    public function testCreateFromDateLocalized(): void
    {
        $time = Time::createFromDate(2017, 03, 05, 'Europe/London');

        $this->assertSame(date('Y-m-d 00:00:00', strtotime('2017-03-05 00:00:00')), $time->toDateTimeString());
    }

    public function testCreateFromTime(): void
    {
        $time = Time::createFromTime(10, 03, 05, 'America/Chicago');

        $this->assertSame(date('Y-m-d 10:03:05'), $time->toDateTimeString());
    }

    public function testCreateFromTimeEvening(): void
    {
        $time = Time::createFromTime(20, 03, 05, 'America/Chicago');

        $this->assertSame(date('Y-m-d 20:03:05'), $time->toDateTimeString());
    }

    public function testCreateFromTimeLocalized(): void
    {
        $time = Time::createFromTime(10, 03, 05, 'Europe/London');

        $this->assertCloseEnoughString(date('Y-m-d 10:03:05'), $time->toDateTimeString());
    }

    public function testCreateFromFormat(): void
    {
        $now = new DateTime('now');

        Time::setTestNow($now);
        $time = Time::createFromFormat('F j, Y', 'January 15, 2017', 'America/Chicago');

        $this->assertCloseEnoughString(date('2017-01-15 H:i:s', $now->getTimestamp()), $time->toDateTimeString());
    }

    public function testCreateFromFormatWithTimezoneString(): void
    {
        $time = Time::createFromFormat('F j, Y', 'January 15, 2017', 'Europe/London');

        $this->assertCloseEnoughString(date('2017-01-15 H:i:s'), $time->toDateTimeString());
    }

    public function testCreateFromFormatWithTimezoneObject(): void
    {
        $tz = new DateTimeZone('Europe/London');

        $time = Time::createFromFormat('F j, Y', 'January 15, 2017', $tz);

        $this->assertCloseEnoughString(date('2017-01-15 H:i:s'), $time->toDateTimeString());
    }

    public function testCreateFromFormatWithInvalidFormat(): void
    {
        $format = 'foobar';

        $this->expectException(I18nException::class);
        $this->expectExceptionMessage(lang('Time.invalidFormat', [$format]));

        Time::createFromFormat($format, 'America/Chicago');
    }

    public function testCreateFromTimestamp(): void
    {
        // Set the timezone temporarily to UTC to make sure the test timestamp is correct
        $tz = date_default_timezone_get();
        date_default_timezone_set('UTC');

        $timestamp = strtotime('2017-03-18 midnight');

        date_default_timezone_set($tz);

        $time = Time::createFromTimestamp($timestamp);

        $this->assertSame(date('2017-03-18 00:00:00'), $time->toDateTimeString());
    }

    public function testCreateFromTimestampWithTimezone(): void
    {
        // Set the timezone temporarily to UTC to make sure the test timestamp is correct
        $tz = date_default_timezone_get();
        date_default_timezone_set('UTC');

        $timestamp = strtotime('2017-03-18 midnight'); // in UTC

        date_default_timezone_set($tz);

        $time = Time::createFromTimestamp($timestamp, 'Asia/Jakarta'); // UTC +7

        $this->assertSame(date('2017-03-18 07:00:00'), $time->toDateTimeString());
    }

    public function testTestNow(): void
    {
        $this->assertFalse(Time::hasTestNow());
        $this->assertCloseEnoughString(date('Y-m-d H:i:s', time()), Time::now()->toDateTimeString());

        $t = new Time('2000-01-02');
        Time::setTestNow($t);

        $this->assertTrue(Time::hasTestNow());
        $this->assertSame('2000-01-02 00:00:00', Time::now()->toDateTimeString());

        Time::setTestNow();
        $this->assertCloseEnoughString(date('Y-m-d H:i:s', time()), Time::now()->toDateTimeString());
    }

    public function testMagicIssetTrue(): void
    {
        $time = Time::parse('January 1, 2016');

        $this->assertTrue(isset($time->year));
    }

    public function testMagicIssetFalse(): void
    {
        $time = Time::parse('January 1, 2016');

        $this->assertFalse(isset($time->foobar));
    }

    public function testGetYear(): void
    {
        $time  = Time::parse('January 1, 2016');
        $time2 = Time::parse('December 31, 2019');

        $this->assertSame('2016', $time->year);
        $this->assertSame('2019', $time2->year);
    }

    public function testGetMonth(): void
    {
        $time = Time::parse('August 1, 2016');

        $this->assertSame('8', $time->month);
    }

    public function testGetDay(): void
    {
        $time = Time::parse('August 12, 2016');

        $this->assertSame('12', $time->day);
    }

    public function testGetHour(): void
    {
        $time = Time::parse('August 12, 2016 4:15pm');

        $this->assertSame('16', $time->hour);
    }

    public function testGetMinute(): void
    {
        $time = Time::parse('August 12, 2016 4:15pm');

        $this->assertSame('15', $time->minute);
    }

    public function testGetSecond(): void
    {
        $time = Time::parse('August 12, 2016 4:15:23pm');

        $this->assertSame('23', $time->second);
    }

    public function testGetDayOfWeek(): void
    {
        $time = Time::parse('August 12, 2016 4:15:23pm');

        $this->assertSame('6', $time->dayOfWeek);
    }

    public function testGetDayOfYear(): void
    {
        $time = Time::parse('August 12, 2016 4:15:23pm');

        $this->assertSame('225', $time->dayOfYear);
    }

    public function testGetWeekOfMonth(): void
    {
        $time = Time::parse('August 12, 2016 4:15:23pm');

        $this->assertSame('2', $time->weekOfMonth);
    }

    public function testGetWeekOfYear(): void
    {
        $time = Time::parse('August 12, 2016 4:15:23pm');

        $this->assertSame('33', $time->weekOfYear);
    }

    public function testGetTimestamp(): void
    {
        $time     = Time::parse('August 12, 2016 4:15:23pm');
        $expected = strtotime('August 12, 2016 4:15:23pm');

        $this->assertSame($expected, $time->timestamp);
    }

    /**
     * This test might fail if your timezone has Daylight Saving Time.
     * See https://github.com/codeigniter4/CodeIgniter4/issues/6818
     */
    public function testGetAge(): void
    {
        $time = Time::parse('5 years ago');

        $this->assertSame(5, $time->getAge());
        $this->assertSame(5, $time->age);
    }

    public function testAgeNow(): void
    {
        $time = new Time();

        $this->assertSame(0, $time->getAge());
    }

    public function testAgeFuture(): void
    {
        Time::setTestNow('June 20, 2022', 'America/Chicago');
        $time = Time::parse('August 12, 2116 4:15:23pm');

        $this->assertSame(0, $time->getAge());
    }

    public function testGetAgeSameDayOfBirthday(): void
    {
        Time::setTestNow('December 31, 2022', 'America/Chicago');
        $time = Time::parse('December 31, 2020');

        $this->assertSame(2, $time->getAge());
    }

    public function testGetAgeNextDayOfBirthday(): void
    {
        Time::setTestNow('January 1, 2022', 'America/Chicago');
        $time = Time::parse('December 31, 2020');

        $this->assertSame(1, $time->getAge());
    }

    public function testGetAgeBeforeDayOfBirthday(): void
    {
        Time::setTestNow('December 30, 2021', 'America/Chicago');
        $time = Time::parse('December 31, 2020');

        $this->assertSame(0, $time->getAge());
    }

    public function testGetQuarter(): void
    {
        $time = Time::parse('April 15, 2015');

        $this->assertSame('2', $time->quarter);
    }

    public function testGetDST(): void
    {
        // America/Chicago. DST from early March -> early Nov
        $time = Time::createFromDate(2012, 1, 1, 'America/Chicago');
        $this->assertFalse($time->dst);

        $time = Time::createFromDate(2012, 9, 1, 'America/Chicago');
        $this->assertTrue($time->dst);
    }

    public function testGetDSTUnobserved(): void
    {
        // Asia/Shanghai. DST not observed
        $tz   = new DateTimeZone('Asia/Shanghai');
        $time = Time::createFromDate(2012, 1, 1, $tz, 'Asia/Shanghai');

        $this->assertFalse($time->dst);
    }

    public function testGetLocal(): void
    {
        $this->assertTrue(Time::now()->local);
        $this->assertFalse(Time::now('Europe/London')->local);
    }

    public function testGetUtc(): void
    {
        $this->assertFalse(Time::now('America/Chicago')->utc);
        $this->assertTrue(Time::now('UTC')->utc);
    }

    public function testGetTimezone(): void
    {
        $instance = Time::now()->getTimezone();

        $this->assertInstanceOf(DateTimeZone::class, $instance);
    }

    public function testGetTimezonename(): void
    {
        $this->assertSame('America/Chicago', Time::now('America/Chicago')->getTimezoneName());
        $this->assertSame('Europe/London', Time::now('Europe/London')->timezoneName);
    }

    public function testSetYear(): void
    {
        $time  = Time::parse('May 10, 2017');
        $time2 = $time->setYear(2015);

        $this->assertInstanceOf(Time::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('2015-05-10 00:00:00', $time2->toDateTimeString());
    }

    public function testSetMonthNumber(): void
    {
        $time  = Time::parse('May 10, 2017');
        $time2 = $time->setMonth(4);

        $this->assertInstanceOf(Time::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('2017-04-10 00:00:00', $time2->toDateTimeString());
    }

    public function testSetMonthLongName(): void
    {
        $time  = Time::parse('May 10, 2017');
        $time2 = $time->setMonth('April');

        $this->assertInstanceOf(Time::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('2017-04-10 00:00:00', $time2->toDateTimeString());
    }

    public function testSetMonthShortName(): void
    {
        $time  = Time::parse('May 10, 2017');
        $time2 = $time->setMonth('Feb');

        $this->assertInstanceOf(Time::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('2017-02-10 00:00:00', $time2->toDateTimeString());
    }

    public function testSetDay(): void
    {
        $time  = Time::parse('May 10, 2017');
        $time2 = $time->setDay(15);

        $this->assertInstanceOf(Time::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('2017-05-15 00:00:00', $time2->toDateTimeString());
    }

    public function testSetDayOverMaxInCurrentMonth(): void
    {
        $this->expectException(I18nException::class);

        $time = Time::parse('Feb 02, 2009');
        $time->setDay(29);
    }

    public function testSetDayNotOverMaxInCurrentMonth(): void
    {
        $time  = Time::parse('Feb 02, 2012');
        $time2 = $time->setDay(29);

        $this->assertInstanceOf(Time::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('2012-02-29 00:00:00', $time2->toDateTimeString());
    }

    public function testSetHour(): void
    {
        $time  = Time::parse('May 10, 2017');
        $time2 = $time->setHour(15);

        $this->assertInstanceOf(Time::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('2017-05-10 15:00:00', $time2->toDateTimeString());
    }

    public function testSetMinute(): void
    {
        $time  = Time::parse('May 10, 2017');
        $time2 = $time->setMinute(30);

        $this->assertInstanceOf(Time::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('2017-05-10 00:30:00', $time2->toDateTimeString());
    }

    public function testSetSecond(): void
    {
        $time  = Time::parse('May 10, 2017');
        $time2 = $time->setSecond(20);

        $this->assertInstanceOf(Time::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('2017-05-10 00:00:20', $time2->toDateTimeString());
    }

    public function testSetMonthTooSmall(): void
    {
        $this->expectException(I18nException::class);

        $time = Time::parse('May 10, 2017');
        $time->setMonth(-5);
    }

    public function testSetMonthTooBig(): void
    {
        $this->expectException(I18nException::class);

        $time = Time::parse('May 10, 2017');
        $time->setMonth(30);
    }

    public function testSetDayTooSmall(): void
    {
        $this->expectException(I18nException::class);

        $time = Time::parse('May 10, 2017');
        $time->setDay(-5);
    }

    public function testSetDayTooBig(): void
    {
        $this->expectException(I18nException::class);

        $time = Time::parse('May 10, 2017');
        $time->setDay(80);
    }

    public function testSetHourTooSmall(): void
    {
        $this->expectException(I18nException::class);

        $time = Time::parse('May 10, 2017');
        $time->setHour(-5);
    }

    public function testSetHourTooBig(): void
    {
        $this->expectException(I18nException::class);

        $time = Time::parse('May 10, 2017');
        $time->setHour(80);
    }

    public function testSetMinuteTooSmall(): void
    {
        $this->expectException(I18nException::class);

        $time = Time::parse('May 10, 2017');
        $time->setMinute(-5);
    }

    public function testSetMinuteTooBig(): void
    {
        $this->expectException(I18nException::class);

        $time = Time::parse('May 10, 2017');
        $time->setMinute(80);
    }

    public function testSetSecondTooSmall(): void
    {
        $this->expectException(I18nException::class);

        $time = Time::parse('May 10, 2017');
        $time->setSecond(-5);
    }

    public function testSetSecondTooBig(): void
    {
        $this->expectException(I18nException::class);

        $time = Time::parse('May 10, 2017');
        $time->setSecond(80);
    }

    public function testSetTimezone(): void
    {
        $time  = Time::parse('May 10, 2017', 'America/Chicago');
        $time2 = $time->setTimezone('Europe/London');

        $this->assertInstanceOf(Time::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('America/Chicago', $time->getTimezoneName());
        $this->assertSame('Europe/London', $time2->getTimezoneName());
    }

    public function testSetTimestamp(): void
    {
        $time  = Time::parse('May 10, 2017', 'America/Chicago');
        $stamp = strtotime('April 1, 2017');
        $time2 = $time->setTimestamp($stamp);

        $this->assertInstanceOf(Time::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('2017-04-01 00:00:00', $time2->toDateTimeString());
    }

    public function testToDateString(): void
    {
        $time = Time::parse('May 10, 2017', 'America/Chicago');
        $this->assertSame('2017-05-10', $time->toDateString());
    }

    public function testToFormattedDateString(): void
    {
        $time = Time::parse('2017-05-10', 'America/Chicago');
        $this->assertSame('May 10, 2017', $time->toFormattedDateString());
    }

    public function testToTimeString(): void
    {
        $time = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $this->assertSame('13:20:33', $time->toTimeString());
    }

    // Add/Subtract

    public function testCanAddSeconds(): void
    {
        $time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->addSeconds(10);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2017-01-10 13:20:43', $newTime->toDateTimeString());
    }

    public function testCanAddMinutes(): void
    {
        $time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->addMinutes(10);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2017-01-10 13:30:33', $newTime->toDateTimeString());
    }

    public function testCanAddHours(): void
    {
        $time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->addHours(3);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2017-01-10 16:20:33', $newTime->toDateTimeString());
    }

    public function testCanAddDays(): void
    {
        $time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->addDays(3);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2017-01-13 13:20:33', $newTime->toDateTimeString());
    }

    public function testCanAddMonths(): void
    {
        $time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->addMonths(3);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2017-04-10 13:20:33', $newTime->toDateTimeString());
    }

    public function testCanAddMonthsOverYearBoundary(): void
    {
        $time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->addMonths(13);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2018-02-10 13:20:33', $newTime->toDateTimeString());
    }

    public function testCanAddYears(): void
    {
        $time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->addYears(3);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2020-01-10 13:20:33', $newTime->toDateTimeString());
    }

    public function testCanSubtractSeconds(): void
    {
        $time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->subSeconds(10);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2017-01-10 13:20:23', $newTime->toDateTimeString());
    }

    public function testCanSubtractMinutes(): void
    {
        $time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->subMinutes(10);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2017-01-10 13:10:33', $newTime->toDateTimeString());
    }

    public function testCanSubtractHours(): void
    {
        $time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->subHours(3);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2017-01-10 10:20:33', $newTime->toDateTimeString());
    }

    public function testCanSubtractDays(): void
    {
        $time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->subDays(3);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2017-01-07 13:20:33', $newTime->toDateTimeString());
    }

    public function testCanSubtractMonths(): void
    {
        $time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->subMonths(3);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2016-10-10 13:20:33', $newTime->toDateTimeString());
    }

    public function testCanSubtractYears(): void
    {
        $time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
        $newTime = $time->subYears(3);
        $this->assertSame('2017-01-10 13:20:33', $time->toDateTimeString());
        $this->assertSame('2014-01-10 13:20:33', $newTime->toDateTimeString());
    }

    // Comparison

    public function testEqualWithDifferent(): void
    {
        $time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
        $time2 = Time::parse('January 11, 2017 03:50:00', 'Europe/London');

        $this->assertTrue($time1->equals($time2));
    }

    public function testEqualWithSame(): void
    {
        $time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
        $time2 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');

        $this->assertTrue($time1->equals($time2));
    }

    public function testEqualWithDateTime(): void
    {
        $time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
        $time2 = new DateTime('January 11, 2017 03:50:00', new DateTimeZone('Europe/London'));

        $this->assertTrue($time1->equals($time2));
    }

    public function testEqualWithSameDateTime(): void
    {
        $time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
        $time2 = new DateTime('January 10, 2017 21:50:00', new DateTimeZone('America/Chicago'));

        $this->assertTrue($time1->equals($time2));
    }

    public function testEqualWithString(): void
    {
        $time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');

        $this->assertTrue($time1->equals('January 11, 2017 03:50:00', 'Europe/London'));
    }

    public function testEqualWithStringAndNotimezone(): void
    {
        $time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');

        $this->assertTrue($time1->equals('January 10, 2017 21:50:00'));
    }

    public function testSameSuccess(): void
    {
        $time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
        $time2 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');

        $this->assertTrue($time1->sameAs($time2));
    }

    public function testSameFailure(): void
    {
        $time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
        $time2 = Time::parse('January 11, 2017 03:50:00', 'Europe/London');

        $this->assertFalse($time1->sameAs($time2));
    }

    public function testSameSuccessAsString(): void
    {
        $time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');

        $this->assertTrue($time1->sameAs('January 10, 2017 21:50:00', 'America/Chicago'));
    }

    public function testSameFailAsString(): void
    {
        $time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');

        $this->assertFalse($time1->sameAs('January 11, 2017 03:50:00', 'Europe/London'));
    }

    public function testBefore(): void
    {
        $time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
        $time2 = Time::parse('January 11, 2017 03:50:00', 'America/Chicago');

        $this->assertTrue($time1->isBefore($time2));
        $this->assertFalse($time2->isBefore($time1));
    }

    public function testAfter(): void
    {
        $time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
        $time2 = Time::parse('January 11, 2017 03:50:00', 'America/Chicago');

        $this->assertFalse($time1->isAfter($time2));
        $this->assertTrue($time2->isAfter($time1));
    }

    public function testHumanizeYearsSingle(): void
    {
        Time::setTestNow('March 10, 2017', 'America/Chicago');
        $time = Time::parse('March 9, 2016 12:00:00', 'America/Chicago');

        $this->assertSame('1 year ago', $time->humanize());
    }

    public function testHumanizeYearsPlural(): void
    {
        Time::setTestNow('March 10, 2017', 'America/Chicago');
        $time = Time::parse('March 9, 2014 12:00:00', 'America/Chicago');

        $this->assertSame('3 years ago', $time->humanize());
    }

    public function testHumanizeYearsForward(): void
    {
        Time::setTestNow('January 1, 2017', 'America/Chicago');
        $time = Time::parse('January 1, 2018 12:00:00', 'America/Chicago');

        $this->assertSame('in 1 year', $time->humanize());
    }

    public function testHumanizeMonthsSingle(): void
    {
        Time::setTestNow('March 10, 2017', 'America/Chicago');
        $time = Time::parse('February 9, 2017', 'America/Chicago');

        $this->assertSame('1 month ago', $time->humanize());
    }

    public function testHumanizeMonthsPlural(): void
    {
        Time::setTestNow('March 1, 2017', 'America/Chicago');
        $time = Time::parse('January 1, 2017', 'America/Chicago');

        $this->assertSame('2 months ago', $time->humanize());
    }

    public function testHumanizeMonthsForward(): void
    {
        Time::setTestNow('March 1, 2017', 'America/Chicago');
        $time = Time::parse('April 1, 2017', 'America/Chicago');

        $this->assertSame('in 1 month', $time->humanize());
    }

    public function testHumanizeDaysSingle(): void
    {
        Time::setTestNow('March 10, 2017', 'America/Chicago');
        $time = Time::parse('March 8, 2017', 'America/Chicago');

        $this->assertSame('2 days ago', $time->humanize());
    }

    public function testHumanizeDaysPlural(): void
    {
        Time::setTestNow('March 10, 2017', 'America/Chicago');
        $time = Time::parse('March 8, 2017', 'America/Chicago');

        $this->assertSame('2 days ago', $time->humanize());
    }

    public function testHumanizeDaysForward(): void
    {
        Time::setTestNow('March 10, 2017', 'America/Chicago');
        $time = Time::parse('March 12, 2017', 'America/Chicago');

        $this->assertSame('in 2 days', $time->humanize());
    }

    public function testHumanizeDaysTomorrow(): void
    {
        Time::setTestNow('March 10, 2017', 'America/Chicago');
        $time = Time::parse('March 11, 2017', 'America/Chicago');

        $this->assertSame('Tomorrow', $time->humanize());
    }

    public function testHumanizeDaysYesterday(): void
    {
        Time::setTestNow('March 10, 2017', 'America/Chicago');
        $time = Time::parse('March 9, 2017', 'America/Chicago');

        $this->assertSame('Yesterday', $time->humanize());
    }

    public function testHumanizeHoursAsTime(): void
    {
        Time::setTestNow('March 10, 2017 12:00', 'America/Chicago');
        $time = Time::parse('March 10, 2017 14:00', 'America/Chicago');

        $this->assertSame('in 2 hours', $time->humanize());
    }

    public function testHumanizeHoursAWhileAgo(): void
    {
        Time::setTestNow('March 10, 2017 12:00', 'America/Chicago');
        $time = Time::parse('March 10, 2017 8:00', 'America/Chicago');

        $this->assertSame('4 hours ago', $time->humanize());
    }

    public function testHumanizeMinutesSingle(): void
    {
        Time::setTestNow('March 10, 2017 12:30', 'America/Chicago');
        $time = Time::parse('March 10, 2017 12:29', 'America/Chicago');

        $this->assertSame('1 minute ago', $time->humanize());
    }

    public function testHumanizeMinutesPlural(): void
    {
        Time::setTestNow('March 10, 2017 12:30', 'America/Chicago');
        $time = Time::parse('March 10, 2017 12:28', 'America/Chicago');

        $this->assertSame('2 minutes ago', $time->humanize());
    }

    public function testHumanizeMinutesForward(): void
    {
        Time::setTestNow('March 10, 2017 12:30', 'America/Chicago');
        $time = Time::parse('March 10, 2017 12:31', 'America/Chicago');

        $this->assertSame('in 1 minute', $time->humanize());
    }

    public function testHumanizeWeeksSingle(): void
    {
        Time::setTestNow('March 10, 2017', 'America/Chicago');
        $time = Time::parse('March 2, 2017', 'America/Chicago');

        $this->assertSame('1 week ago', $time->humanize());
    }

    public function testHumanizeWeeksPlural(): void
    {
        Time::setTestNow('March 30, 2017', 'America/Chicago');
        $time = Time::parse('March 15, 2017', 'America/Chicago');

        $this->assertSame('2 weeks ago', $time->humanize());
    }

    public function testHumanizeWeeksForward(): void
    {
        Time::setTestNow('March 10, 2017', 'America/Chicago');
        $time = Time::parse('March 18, 2017', 'America/Chicago');

        $this->assertSame('in 2 weeks', $time->humanize());
    }

    public function testHumanizeNow(): void
    {
        Time::setTestNow('March 10, 2017', 'America/Chicago');
        $time = Time::parse('March 10, 2017', 'America/Chicago');

        $this->assertSame('Just now', $time->humanize());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4708
     */
    public function testHumanizeWithArLocale(): void
    {
        $this->resetServices();

        $currentLocale = Locale::getDefault();
        Locale::setDefault('ar');

        $config                   = new App();
        $config->supportedLocales = ['ar'];
        $config->defaultLocale    = 'ar';
        Factories::injectMock('config', 'App', $config);

        Time::setTestNow('2022-06-14 12:00', 'America/Chicago');

        $date = '2022-06-07 12:00';
        $time = Time::parse($date, 'America/Chicago');

        $this->assertSame('١ week ago', $time->humanize());

        Locale::setDefault($currentLocale);
    }

    public function testSetTimezoneDate(): void
    {
        $time  = Time::parse('13 May 2020 10:00', 'GMT');
        $time2 = $time->setTimezone('GMT+8');
        $this->assertSame('2020-05-13 10:00:00', $time->toDateTimeString());
        $this->assertSame('2020-05-13 18:00:00', $time2->toDateTimeString());
    }

    public function testCreateFromInstance(): void
    {
        $datetime = new DateTime();
        $time     = Time::createFromInstance($datetime);
        $this->assertInstanceOf(Time::class, $time);
        $this->assertTrue($time->sameAs($datetime));
    }

    public function testGetter(): void
    {
        $time = Time::parse('August 12, 2016 4:15:23pm');

        $this->assertNull($time->weekOfWeek);
    }

    public function testUnserializeTimeObject()
    {
        $time1     = new Time('August 28, 2020 10:04:00pm', 'Asia/Manila', 'en');
        $timeCache = serialize($time1);
        $time2     = unserialize($timeCache);

        $this->assertInstanceOf(Time::class, $time2);
        $this->assertTrue($time2->equals($time1));
        $this->assertNotSame($time1, $time2);
    }

    public function testSetTestNowWithTimeZone(): void
    {
        Time::setTestNow('2017/03/10 12:00', 'Asia/Tokyo');

        $now = Time::now('UTC');

        $this->assertSame('2017-03-10T03:00:00+00:00', $now->format('c'));
    }

    public function testSetTestNowWithFaLocale(): void
    {
        Locale::setDefault('fa');

        Time::setTestNow('2017/03/10 12:00', 'Asia/Tokyo');

        $now = Time::now()->format('c');

        $this->assertSame('2017-03-10T12:00:00+09:00', $now);
    }

    /**
     * @dataProvider provideToStringDoesNotDependOnLocale
     */
    public function testToStringDoesNotDependOnLocale(string $locale): void
    {
        Locale::setDefault($locale);

        $time = new Time('2017/03/10 12:00');

        $this->assertSame('2017-03-10 12:00:00', (string) $time);
    }

    public static function provideToStringDoesNotDependOnLocale(): iterable
    {
        yield from [
            ['en'],
            ['de'],
            ['ar'],
            ['fa'],
        ];
    }

    public function testModify(): void
    {
        $time  = new Time('2017/03/10 12:00');
        $time2 = $time->modify('+1 day');

        $this->assertInstanceOf(Time::class, $time2);
        $this->assertNotSame($time, $time2);
        $this->assertSame('2017-03-10 12:00:00', (string) $time);
        $this->assertSame('2017-03-11 12:00:00', (string) $time2);
    }
}
