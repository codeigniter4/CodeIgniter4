<?php
namespace CodeIgniter\I18n;

use DateTime;
use DateTimeZone;
use IntlDateFormatter;

class TimeTest extends \CodeIgniter\Test\CIUnitTestCase
{

	protected function setUp(): void
	{
		parent::setUp();

		helper('date');
		\Locale::setDefault('America/Chicago');
	}

	public function testNewTimeNow()
	{
		$formatter = new IntlDateFormatter(
				'en_US', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT, 'America/Chicago', // Default for CodeIgniter
				IntlDateFormatter::GREGORIAN, 'yyyy-MM-dd HH:mm:ss'
		);

		$time = new Time(null, 'America/Chicago');

		$this->assertEquals($formatter->format($time), (string) $time);
	}

	public function testTimeWithTimezone()
	{
		$formatter = new IntlDateFormatter(
				'en_US', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT, 'Europe/London', // Default for CodeIgniter
				IntlDateFormatter::GREGORIAN, 'yyyy-MM-dd HH:mm:ss'
		);

		$time = new Time('now', 'Europe/London');

		$this->assertEquals($formatter->format($time), (string) $time);
	}

	public function testTimeWithTimezoneAndLocale()
	{
		$formatter = new IntlDateFormatter(
				'fr_FR', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT, 'Europe/London', // Default for CodeIgniter
				IntlDateFormatter::GREGORIAN, 'yyyy-MM-dd HH:mm:ss'
		);

		$time = new Time('now', 'Europe/London', 'fr_FR');

		$this->assertEquals($formatter->format($time), (string) $time);
	}

	public function testTimeWithDateTimeZone()
	{
		$formatter = new IntlDateFormatter(
				'fr_FR', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT, 'Europe/London', IntlDateFormatter::GREGORIAN, 'yyyy-MM-dd HH:mm:ss'
		);

		$time = new Time('now', new \DateTimeZone('Europe/London'), 'fr_FR');

		$this->assertEquals($formatter->format($time), (string) $time);
	}

	public function testToDateTime()
	{
		$time = new Time();

		$obj = $time->toDateTime();

		$this->assertInstanceOf(\DateTime::class, $obj);
	}

	public function testNow()
	{
		$time  = Time::now();
		$time1 = new \DateTime();

		$this->assertInstanceOf(Time::class, $time);
		$this->assertEquals($time->getTimestamp(), $time1->getTimestamp());
	}

	public function testParse()
	{
		$time  = Time::parse('next Tuesday', 'America/Chicago');
		$time1 = new \DateTime('now', new \DateTimeZone('America/Chicago'));
		$time1->modify('next Tuesday');

		$this->assertEquals($time->getTimestamp(), $time1->getTimestamp());
	}

	public function testToDateTimeString()
	{
		$time = Time::parse('2017-01-12 00:00', 'America/Chicago');

		$this->assertEquals('2017-01-12 00:00:00', (string) $time);
		$this->assertEquals('2017-01-12 00:00:00', $time->toDateTimeString());
	}

	public function testToDateTimeStringWithTimeZone()
	{
		$time = Time::parse('2017-01-12 00:00', 'Europe/London');

		$expects = new \DateTime('2017-01-12', new \DateTimeZone('Europe/London'));

		$this->assertEquals($expects->format('Y-m-d H:i:s'), $time->toDateTimeString());
	}

	public function testToday()
	{
		$time = Time::today();

		$this->assertEquals(date('Y-m-d 00:00:00'), $time->toDateTimeString());
	}

	public function testTodayLocalized()
	{
		$time = Time::today('Europe/London');

		$this->assertEquals(date('Y-m-d 00:00:00'), $time->toDateTimeString());
	}

	public function testYesterday()
	{
		$time = Time::yesterday();

		$this->assertEquals(date('Y-m-d 00:00:00', strtotime('-1 day')), $time->toDateTimeString());
	}

	public function testTomorrow()
	{
		$time = Time::tomorrow();

		$this->assertEquals(date('Y-m-d 00:00:00', strtotime('+1 day')), $time->toDateTimeString());
	}

	public function testCreateFromDate()
	{
		$time = Time::createFromDate(2017, 03, 05, 'America/Chicago');

		$this->assertEquals(date('Y-m-d 00:00:00', strtotime('2017-03-05 00:00:00')), $time->toDateTimeString());
	}

	public function testCreateFromDateLocalized()
	{
		$time = Time::createFromDate(2017, 03, 05, 'Europe/London');

		$this->assertEquals(date('Y-m-d 00:00:00', strtotime('2017-03-05 00:00:00')), $time->toDateTimeString());
	}

	public function testCreateFromTime()
	{
		$time = Time::createFromTime(10, 03, 05, 'America/Chicago');

		$this->assertEquals(date('Y-m-d 10:03:05'), $time->toDateTimeString());
	}

	public function testCreateFromTimeEvening()
	{
		$time = Time::createFromTime(20, 03, 05, 'America/Chicago');

		$this->assertEquals(date('Y-m-d 20:03:05'), $time->toDateTimeString());
	}

	public function testCreateFromTimeLocalized()
	{
		$time = Time::createFromTime(10, 03, 05, 'Europe/London');

		$this->assertCloseEnoughString(date('Y-m-d 10:03:05'), $time->toDateTimeString());
	}

	public function testCreateFromFormat()
	{
		$now = new \DateTime('now');

		Time::setTestNow($now);
		$time = Time::createFromFormat('F j, Y', 'January 15, 2017', 'America/Chicago');

		$this->assertCloseEnoughString(date('2017-01-15 H:i:s', $now->getTimestamp()), $time->toDateTimeString());
		Time::setTestNow();
	}

	public function testCreateFromFormatWithTimezoneString()
	{
		$time = Time::createFromFormat('F j, Y', 'January 15, 2017', 'Europe/London');

		$this->assertCloseEnoughString(date('2017-01-15 H:i:s'), $time->toDateTimeString());
	}

	public function testCreateFromFormatWithTimezoneObject()
	{
		$tz = new \DateTimeZone('Europe/London');

		$time = Time::createFromFormat('F j, Y', 'January 15, 2017', $tz);

		$this->assertCloseEnoughString(date('2017-01-15 H:i:s'), $time->toDateTimeString());
	}

	public function testCreateFromTimestamp()
	{
		$time = Time::createFromTimestamp(strtotime('2017-03-18 midnight'));

		$this->assertEquals(date('2017-03-18 00:00:00'), $time->toDateTimeString());
	}

	public function testTestNow()
	{
		$this->assertFalse(Time::hasTestNow());
		$this->assertCloseEnoughString(date('Y-m-d H:i:s', time()), Time::now()->toDateTimeString());

		$t = new Time('2000-01-02');
		Time::setTestNow($t);

		$this->assertTrue(Time::hasTestNow());
		$this->assertEquals('2000-01-02 00:00:00', Time::now()->toDateTimeString());

		Time::setTestNow();
		$this->assertCloseEnoughString(date('Y-m-d H:i:s', time()), Time::now()->toDateTimeString());
	}

	//--------------------------------------------------------------------

	public function testMagicIssetTrue()
	{
		$time = Time::parse('January 1, 2016');

		$this->assertTrue(isset($time->year));
	}

	public function testMagicIssetFalse()
	{
		$time = Time::parse('January 1, 2016');

		$this->assertFalse(isset($time->foobar));
	}

	//--------------------------------------------------------------------

	public function testGetYear()
	{
		$time  = Time::parse('January 1, 2016');
		$time2 = Time::parse('December 31, 2019');

		$this->assertEquals(2016, $time->year);
		$this->assertEquals(2019, $time2->year);
	}

	public function testGetMonth()
	{
		$time = Time::parse('August 1, 2016');

		$this->assertEquals(8, $time->month);
	}

	public function testGetDay()
	{
		$time = Time::parse('August 12, 2016');

		$this->assertEquals(12, $time->day);
	}

	public function testGetHour()
	{
		$time = Time::parse('August 12, 2016 4:15pm');

		$this->assertEquals(16, $time->hour);
	}

	public function testGetMinute()
	{
		$time = Time::parse('August 12, 2016 4:15pm');

		$this->assertEquals(15, $time->minute);
	}

	public function testGetSecond()
	{
		$time = Time::parse('August 12, 2016 4:15:23pm');

		$this->assertEquals(23, $time->second);
	}

	public function testGetDayOfWeek()
	{
		$time = Time::parse('August 12, 2016 4:15:23pm');

		$this->assertEquals(6, $time->dayOfWeek);
	}

	public function testGetDayOfYear()
	{
		$time = Time::parse('August 12, 2016 4:15:23pm');

		$this->assertEquals(225, $time->dayOfYear);
	}

	public function testGetWeekOfMonth()
	{
		$time = Time::parse('August 12, 2016 4:15:23pm');

		$this->assertEquals(2, $time->weekOfMonth);
	}

	public function testGetWeekOfYear()
	{
		$time = Time::parse('August 12, 2016 4:15:23pm');

		$this->assertEquals(33, $time->weekOfYear);
	}

	public function testGetTimestamp()
	{
		$time     = Time::parse('August 12, 2016 4:15:23pm');
		$expected = strtotime('August 12, 2016 4:15:23pm');

		$this->assertEquals($expected, $time->timestamp);
	}

	public function testGetAge()
	{
		$time = Time::parse('5 years ago');
		$this->assertEquals(5, $time->age);
	}

	public function testAgeNow()
	{
		$time = new Time();
		$this->assertEquals(0, $time->age);
	}

	public function testAgeFuture()
	{
		$time = Time::parse('August 12, 2116 4:15:23pm');
		$this->assertEquals(0, $time->age);
	}

	public function testGetQuarter()
	{
		$time = Time::parse('April 15, 2015');

		$this->assertEquals(2, $time->quarter);
	}

	public function testGetDST()
	{
		// America/Chicago. DST from early March -> early Nov
		$time = Time::createFromDate(2012, 1, 1);
		$this->assertFalse($time->dst);
		$time = Time::createFromDate(2012, 9, 1);
		$this->assertTrue($time->dst);
	}

	public function testGetDSTUnobserved()
	{
		// Asia/Shanghai. DST not observed
		$tz   = new DateTimeZone('Asia/Shanghai');
		$time = Time::createFromDate(2012, 1, 1, $tz, 'Asia/Shanghai');

		$this->assertFalse($time->dst);
	}

	public function testGetLocal()
	{
		$this->assertTrue(Time::now()->local);
		$this->assertFalse(Time::now('Europe/London')->local);
	}

	public function testGetUtc()
	{
		$this->assertFalse(Time::now('America/Chicago')->utc);
		$this->assertTrue(Time::now('UTC')->utc);
	}

	public function testGetTimezone()
	{
		$instance = Time::now()->getTimezone();

		$this->assertInstanceOf(\DateTimeZone::class, $instance);
	}

	public function testGetTimezonename()
	{
		$this->assertEquals('America/Chicago', Time::now('America/Chicago')->getTimezoneName());
		$this->assertEquals('Europe/London', Time::now('Europe/London')->timezoneName);
	}

	public function testSetYear()
	{
		$time  = Time::parse('May 10, 2017');
		$time2 = $time->setYear(2015);

		$this->assertInstanceOf(Time::class, $time2);
		$this->assertNotSame($time, $time2);
		$this->assertEquals('2015-05-10 00:00:00', $time2->toDateTimeString());
	}

	public function testSetMonthNumber()
	{
		$time  = Time::parse('May 10, 2017');
		$time2 = $time->setMonth(4);

		$this->assertInstanceOf(Time::class, $time2);
		$this->assertNotSame($time, $time2);
		$this->assertEquals('2017-04-10 00:00:00', $time2->toDateTimeString());
	}

	public function testSetMonthLongName()
	{
		$time  = Time::parse('May 10, 2017');
		$time2 = $time->setMonth('April');

		$this->assertInstanceOf(Time::class, $time2);
		$this->assertNotSame($time, $time2);
		$this->assertEquals('2017-04-10 00:00:00', $time2->toDateTimeString());
	}

	public function testSetMonthShortName()
	{
		$time  = Time::parse('May 10, 2017');
		$time2 = $time->setMonth('Feb');

		$this->assertInstanceOf(Time::class, $time2);
		$this->assertNotSame($time, $time2);
		$this->assertEquals('2017-02-10 00:00:00', $time2->toDateTimeString());
	}

	public function testSetDay()
	{
		$time  = Time::parse('May 10, 2017');
		$time2 = $time->setDay(15);

		$this->assertInstanceOf(Time::class, $time2);
		$this->assertNotSame($time, $time2);
		$this->assertEquals('2017-05-15 00:00:00', $time2->toDateTimeString());
	}

	public function testSetDayOverMaxInCurrentMonth()
	{
		$this->expectException('CodeIgniter\I18n\Exceptions\I18nException');

		$time = Time::parse('Feb 02, 2009');
		$time->setDay(29);
	}

	public function testSetDayNotOverMaxInCurrentMonth()
	{
		$time  = Time::parse('Feb 02, 2012');
		$time2 = $time->setDay(29);

		$this->assertInstanceOf(Time::class, $time2);
		$this->assertNotSame($time, $time2);
		$this->assertEquals('2012-02-29 00:00:00', $time2->toDateTimeString());
	}

	public function testSetHour()
	{
		$time  = Time::parse('May 10, 2017');
		$time2 = $time->setHour(15);

		$this->assertInstanceOf(Time::class, $time2);
		$this->assertNotSame($time, $time2);
		$this->assertEquals('2017-05-10 15:00:00', $time2->toDateTimeString());
	}

	public function testSetMinute()
	{
		$time  = Time::parse('May 10, 2017');
		$time2 = $time->setMinute(30);

		$this->assertInstanceOf(Time::class, $time2);
		$this->assertNotSame($time, $time2);
		$this->assertEquals('2017-05-10 00:30:00', $time2->toDateTimeString());
	}

	public function testSetSecond()
	{
		$time  = Time::parse('May 10, 2017');
		$time2 = $time->setSecond(20);

		$this->assertInstanceOf(Time::class, $time2);
		$this->assertNotSame($time, $time2);
		$this->assertEquals('2017-05-10 00:00:20', $time2->toDateTimeString());
	}

	public function testSetMonthTooSmall()
	{
		$this->expectException('CodeIgniter\I18n\Exceptions\I18nException');

		$time = Time::parse('May 10, 2017');
		$time->setMonth(-5);
	}

	public function testSetMonthTooBig()
	{
		$this->expectException('CodeIgniter\I18n\Exceptions\I18nException');

		$time = Time::parse('May 10, 2017');
		$time->setMonth(30);
	}

	public function testSetDayTooSmall()
	{
		$this->expectException('CodeIgniter\I18n\Exceptions\I18nException');

		$time = Time::parse('May 10, 2017');
		$time->setDay(-5);
	}

	public function testSetDayTooBig()
	{
		$this->expectException('CodeIgniter\I18n\Exceptions\I18nException');

		$time = Time::parse('May 10, 2017');
		$time->setDay(80);
	}

	public function testSetHourTooSmall()
	{
		$this->expectException('CodeIgniter\I18n\Exceptions\I18nException');

		$time = Time::parse('May 10, 2017');
		$time->setHour(-5);
	}

	public function testSetHourTooBig()
	{
		$this->expectException('CodeIgniter\I18n\Exceptions\I18nException');

		$time = Time::parse('May 10, 2017');
		$time->setHour(80);
	}

	public function testSetMinuteTooSmall()
	{
		$this->expectException('CodeIgniter\I18n\Exceptions\I18nException');

		$time = Time::parse('May 10, 2017');
		$time->setMinute(-5);
	}

	public function testSetMinuteTooBig()
	{
		$this->expectException('CodeIgniter\I18n\Exceptions\I18nException');

		$time = Time::parse('May 10, 2017');
		$time->setMinute(80);
	}

	public function testSetSecondTooSmall()
	{
		$this->expectException('CodeIgniter\I18n\Exceptions\I18nException');

		$time = Time::parse('May 10, 2017');
		$time->setSecond(-5);
	}

	public function testSetSecondTooBig()
	{
		$this->expectException('CodeIgniter\I18n\Exceptions\I18nException');

		$time = Time::parse('May 10, 2017');
		$time->setSecond(80);
	}

	public function testSetTimezone()
	{
		$time  = Time::parse('May 10, 2017', 'America/Chicago');
		$time2 = $time->setTimezone('Europe/London');

		$this->assertInstanceOf(Time::class, $time2);
		$this->assertNotSame($time, $time2);
		$this->assertEquals('America/Chicago', $time->getTimezoneName());
		$this->assertEquals('Europe/London', $time2->getTimezoneName());
	}

	public function testSetTimestamp()
	{
		$time  = Time::parse('May 10, 2017', 'America/Chicago');
		$stamp = strtotime('April 1, 2017');
		$time2 = $time->setTimestamp($stamp);

		$this->assertInstanceOf(Time::class, $time2);
		$this->assertNotSame($time, $time2);
		$this->assertEquals('2017-04-01 00:00:00', $time2->toDateTimeString());
	}

	public function testToDateString()
	{
		$time = Time::parse('May 10, 2017', 'America/Chicago');
		$this->assertEquals('2017-05-10', $time->toDateString());
	}

	public function testToFormattedDateString()
	{
		$time = Time::parse('2017-05-10', 'America/Chicago');
		$this->assertEquals('May 10, 2017', $time->toFormattedDateString());
	}

	/**
	 * Unfortunately, ubuntu 14.04 (on TravisCI) fails this test and
	 * shows a numeric version of the month instead of the textual version.
	 * Confirmed on CentOS 7 as well.
	 * Example: format 'MMM' for November returns 'M02' instead of 'Nov'
	 * Not sure what the fix is just yet....
	 */
	//    public function testToFormattedDateString()
	//    {
	//        $time = Time::parse('February 10, 2017', 'America/Chicago');
	//        $this->assertEquals('Feb 10, 2017', $time->toFormattedDateString());
	//    }

	public function testToTimeString()
	{
		$time = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
		$this->assertEquals('13:20:33', $time->toTimeString());
	}

	//--------------------------------------------------------------------
	// Add/Subtract
	//--------------------------------------------------------------------

	public function testCanAddSeconds()
	{
		$time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
		$newTime = $time->addSeconds(10);
		$this->assertEquals('2017-01-10 13:20:33', $time->toDateTimeString());
		$this->assertEquals('2017-01-10 13:20:43', $newTime->toDateTimeString());
	}

	public function testCanAddMinutes()
	{
		$time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
		$newTime = $time->addMinutes(10);
		$this->assertEquals('2017-01-10 13:20:33', $time->toDateTimeString());
		$this->assertEquals('2017-01-10 13:30:33', $newTime->toDateTimeString());
	}

	public function testCanAddHours()
	{
		$time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
		$newTime = $time->addHours(3);
		$this->assertEquals('2017-01-10 13:20:33', $time->toDateTimeString());
		$this->assertEquals('2017-01-10 16:20:33', $newTime->toDateTimeString());
	}

	public function testCanAddDays()
	{
		$time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
		$newTime = $time->addDays(3);
		$this->assertEquals('2017-01-10 13:20:33', $time->toDateTimeString());
		$this->assertEquals('2017-01-13 13:20:33', $newTime->toDateTimeString());
	}

	public function testCanAddMonths()
	{
		$time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
		$newTime = $time->addMonths(3);
		$this->assertEquals('2017-01-10 13:20:33', $time->toDateTimeString());
		$this->assertEquals('2017-04-10 13:20:33', $newTime->toDateTimeString());
	}

	public function testCanAddMonthsOverYearBoundary()
	{
		$time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
		$newTime = $time->addMonths(13);
		$this->assertEquals('2017-01-10 13:20:33', $time->toDateTimeString());
		$this->assertEquals('2018-02-10 13:20:33', $newTime->toDateTimeString());
	}

	public function testCanAddYears()
	{
		$time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
		$newTime = $time->addYears(3);
		$this->assertEquals('2017-01-10 13:20:33', $time->toDateTimeString());
		$this->assertEquals('2020-01-10 13:20:33', $newTime->toDateTimeString());
	}

	public function testCanSubtractSeconds()
	{
		$time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
		$newTime = $time->subSeconds(10);
		$this->assertEquals('2017-01-10 13:20:33', $time->toDateTimeString());
		$this->assertEquals('2017-01-10 13:20:23', $newTime->toDateTimeString());
	}

	public function testCanSubtractMinutes()
	{
		$time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
		$newTime = $time->subMinutes(10);
		$this->assertEquals('2017-01-10 13:20:33', $time->toDateTimeString());
		$this->assertEquals('2017-01-10 13:10:33', $newTime->toDateTimeString());
	}

	public function testCanSubtractHours()
	{
		$time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
		$newTime = $time->subHours(3);
		$this->assertEquals('2017-01-10 13:20:33', $time->toDateTimeString());
		$this->assertEquals('2017-01-10 10:20:33', $newTime->toDateTimeString());
	}

	public function testCanSubtractDays()
	{
		$time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
		$newTime = $time->subDays(3);
		$this->assertEquals('2017-01-10 13:20:33', $time->toDateTimeString());
		$this->assertEquals('2017-01-07 13:20:33', $newTime->toDateTimeString());
	}

	public function testCanSubtractMonths()
	{
		$time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
		$newTime = $time->subMonths(3);
		$this->assertEquals('2017-01-10 13:20:33', $time->toDateTimeString());
		$this->assertEquals('2016-10-10 13:20:33', $newTime->toDateTimeString());
	}

	public function testCanSubtractYears()
	{
		$time    = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
		$newTime = $time->subYears(3);
		$this->assertEquals('2017-01-10 13:20:33', $time->toDateTimeString());
		$this->assertEquals('2014-01-10 13:20:33', $newTime->toDateTimeString());
	}

	//--------------------------------------------------------------------
	// Comparison
	//--------------------------------------------------------------------

	public function testEqualWithDifferent()
	{
		$time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
		$time2 = Time::parse('January 11, 2017 03:50:00', 'Europe/London');

		$this->assertTrue($time1->equals($time2));
	}

	public function testEqualWithSame()
	{
		$time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
		$time2 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');

		$this->assertTrue($time1->equals($time2));
	}

	public function testEqualWithDateTime()
	{
		$time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
		$time2 = new \DateTime('January 11, 2017 03:50:00', new \DateTimeZone('Europe/London'));

		$this->assertTrue($time1->equals($time2));
	}

	public function testEqualWithSameDateTime()
	{
		$time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
		$time2 = new \DateTime('January 10, 2017 21:50:00', new \DateTimeZone('America/Chicago'));

		$this->assertTrue($time1->equals($time2));
	}

	public function testEqualWithString()
	{
		$time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');

		$this->assertTrue($time1->equals('January 11, 2017 03:50:00', 'Europe/London'));
	}

	public function testEqualWithStringAndNotimezone()
	{
		$time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');

		$this->assertTrue($time1->equals('January 10, 2017 21:50:00'));
	}

	public function testSameSuccess()
	{
		$time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
		$time2 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');

		$this->assertTrue($time1->sameAs($time2));
	}

	public function testSameFailure()
	{
		$time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
		$time2 = Time::parse('January 11, 2017 03:50:00', 'Europe/London');

		$this->assertFalse($time1->sameAs($time2));
	}

	public function testSameSuccessAsString()
	{
		$time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');

		$this->assertTrue($time1->sameAs('January 10, 2017 21:50:00', 'America/Chicago'));
	}

	public function testSameFailAsString()
	{
		$time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');

		$this->assertFalse($time1->sameAs('January 11, 2017 03:50:00', 'Europe/London'));
	}

	public function testBefore()
	{
		$time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
		$time2 = Time::parse('January 11, 2017 03:50:00', 'America/Chicago');

		$this->assertTrue($time1->isBefore($time2));
		$this->assertFalse($time2->isBefore($time1));
	}

	public function testAfter()
	{
		$time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
		$time2 = Time::parse('January 11, 2017 03:50:00', 'America/Chicago');

		$this->assertFalse($time1->isAfter($time2));
		$this->assertTrue($time2->isAfter($time1));
	}

	public function testHumanizeYearsSingle()
	{
		Time::setTestNow('March 10, 2017', 'America/Chicago');
		$time = Time::parse('March 9, 2016 12:00:00', 'America/Chicago');

		$this->assertEquals('1 year ago', $time->humanize());
	}

	public function testHumanizeYearsPlural()
	{
		Time::setTestNow('March 10, 2017', 'America/Chicago');
		$time = Time::parse('March 9, 2014 12:00:00', 'America/Chicago');

		$this->assertEquals('3 years ago', $time->humanize());
	}

	public function testHumanizeYearsForward()
	{
		Time::setTestNow('January 1, 2017', 'America/Chicago');
		$time = Time::parse('January 1, 2018 12:00:00', 'America/Chicago');

		$this->assertEquals('in 1 year', $time->humanize());
	}

	public function testHumanizeMonthsSingle()
	{
		Time::setTestNow('March 10, 2017', 'America/Chicago');
		$time = Time::parse('February 9, 2017', 'America/Chicago');

		$this->assertEquals('1 month ago', $time->humanize());
	}

	public function testHumanizeMonthsPlural()
	{
		Time::setTestNow('March 1, 2017', 'America/Chicago');
		$time = Time::parse('January 1, 2017', 'America/Chicago');

		$this->assertEquals('2 months ago', $time->humanize());
	}

	public function testHumanizeMonthsForward()
	{
		Time::setTestNow('March 1, 2017', 'America/Chicago');
		$time = Time::parse('April 1, 2017', 'America/Chicago');

		$this->assertEquals('in 1 month', $time->humanize());
	}

	public function testHumanizeDaysSingle()
	{
		Time::setTestNow('March 10, 2017', 'America/Chicago');
		$time = Time::parse('March 8, 2017', 'America/Chicago');

		$this->assertEquals('2 days ago', $time->humanize());
	}

	public function testHumanizeDaysPlural()
	{
		Time::setTestNow('March 10, 2017', 'America/Chicago');
		$time = Time::parse('March 8, 2017', 'America/Chicago');

		$this->assertEquals('2 days ago', $time->humanize());
	}

	public function testHumanizeDaysForward()
	{
		Time::setTestNow('March 10, 2017', 'America/Chicago');
		$time = Time::parse('March 12, 2017', 'America/Chicago');

		$this->assertEquals('in 2 days', $time->humanize());
	}

	public function testHumanizeDaysTomorrow()
	{
		Time::setTestNow('March 10, 2017', 'America/Chicago');
		$time = Time::parse('March 11, 2017', 'America/Chicago');

		$this->assertEquals('Tomorrow', $time->humanize());
	}

	public function testHumanizeDaysYesterday()
	{
		Time::setTestNow('March 10, 2017', 'America/Chicago');
		$time = Time::parse('March 9, 2017', 'America/Chicago');

		$this->assertEquals('Yesterday', $time->humanize());
	}

	public function testHumanizeHoursAsTime()
	{
		Time::setTestNow('March 10, 2017 12:00', 'America/Chicago');
		$time = Time::parse('March 10, 2017 14:00', 'America/Chicago');

		$this->assertEquals('in 2 hours', $time->humanize());
	}

	public function testHumanizeHoursAWhileAgo()
	{
		Time::setTestNow('March 10, 2017 12:00', 'America/Chicago');
		$time = Time::parse('March 10, 2017 8:00', 'America/Chicago');

		$this->assertEquals('4 hours ago', $time->humanize());
	}

	public function testHumanizeMinutesSingle()
	{
		Time::setTestNow('March 10, 2017 12:30', 'America/Chicago');
		$time = Time::parse('March 10, 2017 12:29', 'America/Chicago');

		$this->assertEquals('1 minute ago', $time->humanize());
	}

	public function testHumanizeMinutesPlural()
	{
		Time::setTestNow('March 10, 2017 12:30', 'America/Chicago');
		$time = Time::parse('March 10, 2017 12:28', 'America/Chicago');

		$this->assertEquals('2 minutes ago', $time->humanize());
	}

	public function testHumanizeMinutesForward()
	{
		Time::setTestNow('March 10, 2017 12:30', 'America/Chicago');
		$time = Time::parse('March 10, 2017 12:31', 'America/Chicago');

		$this->assertEquals('in 1 minute', $time->humanize());
	}

	public function testHumanizeWeeksSingle()
	{
		Time::setTestNow('March 10, 2017', 'America/Chicago');
		$time = Time::parse('March 2, 2017', 'America/Chicago');

		$this->assertEquals('1 week ago', $time->humanize());
	}

	public function testHumanizeWeeksPlural()
	{
		Time::setTestNow('March 30, 2017', 'America/Chicago');
		$time = Time::parse('March 15, 2017', 'America/Chicago');

		$this->assertEquals('2 weeks ago', $time->humanize());
	}

	public function testHumanizeWeeksForward()
	{
		Time::setTestNow('March 10, 2017', 'America/Chicago');
		$time = Time::parse('March 18, 2017', 'America/Chicago');

		$this->assertEquals('in 2 weeks', $time->humanize());
	}

	public function testHumanizeNow()
	{
		Time::setTestNow('March 10, 2017', 'America/Chicago');
		$time = Time::parse('March 10, 2017', 'America/Chicago');

		$this->assertEquals('Just now', $time->humanize());
	}

	public function testSetTimezoneDate()
	{
		$time  = Time::parse('13 May 2020 10:00', 'GMT');
		$time2 = $time->setTimezone('GMT+8');
		$this->assertEquals('2020-05-13 10:00:00', $time->toDateTimeString());
		$this->assertEquals('2020-05-13 18:00:00', $time2->toDateTimeString());
	}

	//--------------------------------------------------------------------
	// Missing tests

	public function testInstance()
	{
		$datetime = new DateTime();
		$time     = Time::instance($datetime);
		$this->assertTrue($time instanceof Time);
		$this->assertTrue($time->sameAs($datetime));
	}

	public function testGetter()
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
		$this->assertEquals($time1, $time2);
	}
}
