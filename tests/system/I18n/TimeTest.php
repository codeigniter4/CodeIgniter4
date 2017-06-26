<?php namespace CodeIgniter\I18n;

use IntlDateFormatter;

class TimeTest extends \CIUnitTestCase
{
	public function setUp()
	{
		parent::setUp();

		helper('date');
		\Locale::setDefault('America/Chicago');
	}

	public function testNewTimeNow()
	{
		$time = new Time();

		$formatter = new IntlDateFormatter(
			'en_US',
			IntlDateFormatter::SHORT,
			IntlDateFormatter::SHORT,
			'America/Chicago',  // Default for CodeIgniter
			IntlDateFormatter::GREGORIAN,
			'yyyy-MM-dd HH:mm:ss'
		);

		$this->assertEquals($formatter->format(strtotime('now')), (string)$time);
	}

	public function testTimeWithTimezone()
	{
		$time = new Time('now', 'Europe/London');

		$formatter = new IntlDateFormatter(
			'en_US',
			IntlDateFormatter::SHORT,
			IntlDateFormatter::SHORT,
			'Europe/London',  // Default for CodeIgniter
			IntlDateFormatter::GREGORIAN,
			'yyyy-MM-dd HH:mm:ss'
		);

		$this->assertEquals($formatter->format(strtotime('now')), (string)$time);
	}

	public function testTimeWithTimezoneAndLocale()
	{
		$time = new Time('now', 'Europe/London', 'fr_FR');

		$formatter = new IntlDateFormatter(
			'fr_FR',
			IntlDateFormatter::SHORT,
			IntlDateFormatter::SHORT,
			'Europe/London',  // Default for CodeIgniter
			IntlDateFormatter::GREGORIAN,
			'yyyy-MM-dd HH:mm:ss'
		);

		$this->assertEquals($formatter->format(strtotime('now')), (string)$time);
	}

	public function testTimeWithDateTimeZone()
	{
		$time = new Time('now', new \DateTimeZone('Europe/London'), 'fr_FR');

		$formatter = new IntlDateFormatter(
			'fr_FR',
			IntlDateFormatter::SHORT,
			IntlDateFormatter::SHORT,
			'Europe/London',
			IntlDateFormatter::GREGORIAN,
			'yyyy-MM-dd HH:mm:ss'
		);

		$this->assertEquals($formatter->format(strtotime('now')), (string)$time);
	}

	public function testToDateTime()
	{
		$time = new Time();

		$obj = $time->toDateTime();

		$this->assertTrue($obj instanceof \DateTime);
	}

	public function testNow()
	{
		$time = Time::now();
		$time1 = new \DateTime();

		$this->assertTrue($time instanceof Time);
		$this->assertEquals($time->getTimestamp(), $time1->getTimestamp());
	}

	public function testParse()
	{
		$time = Time::parse('next Tuesday', 'America/Chicago');
		$time1 = new \DateTime();
		$time1->modify('next Tuesday');

		$this->assertEquals($time->getTimestamp(), $time1->getTimestamp());
	}

	public function testToDateTimeString()
	{
		$time = Time::parse('2017-01-12 00:00', 'America/Chicago');

		$this->assertEquals('2017-01-12 00:00:00', (string)$time);
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

		$this->assertEquals(date('Y-m-d 10:03:05'), $time->toDateTimeString());
	}

	public function testCreateFromFormat()
	{
		$time = Time::createFromFormat('F j, Y', 'January 15, 2017', 'America/Chicago');

		$this->assertEquals(date('2017-01-15 H:i:s'), $time->toDateTimeString());
	}

	public function testCreateFromFormatWithTimezoneString()
	{
		$time = Time::createFromFormat('F j, Y', 'January 15, 2017', 'Europe/London');

		$this->assertEquals(date('2017-01-15 H:i:s'), $time->toDateTimeString());
	}

	public function testCreateFromFormatWithTimezoneObject()
	{
		$tz = new \DateTimeZone('Europe/London');

		$time = Time::createFromFormat('F j, Y', 'January 15, 2017', $tz);

		$this->assertEquals(date('2017-01-15 H:i:s'), $time->toDateTimeString());
	}

	public function testCreateFromTimestamp()
	{
		$time = Time::createFromTimestamp(strtotime('2017-03-18 midnight'));

		$this->assertEquals(date('2017-03-18 00:00:00'), $time->toDateTimeString());
	}

	public function testTestNow()
	{
		$this->assertFalse(Time::hasTestNow());
		$this->assertEquals(date('Y-m-d H:i:s', time()), Time::now()->toDateTimeString());

		$t = new Time('2000-01-02');
		Time::setTestNow($t);

		$this->assertTrue(Time::hasTestNow());
		$this->assertEquals('2000-01-02 00:00:00', Time::now()->toDateTimeString());

		Time::setTestNow();
		$this->assertEquals(date('Y-m-d H:i:s', time()), Time::now()->toDateTimeString());
	}
	
	//--------------------------------------------------------------------

	public function testGetYear()
	{
		$time = Time::parse('January 1, 2016');

		$this->assertEquals(2016, $time->year);
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
		$time = Time::parse('August 12, 2016 4:15:23pm');
		$expected = strtotime('August 12, 2016 4:15:23pm');

		$this->assertEquals($expected, $time->timestamp);
	}

	public function testGetAge()
	{
		$time = Time::parse('5 years ago');

		$this->assertEquals(5, $time->age);
	}

	public function testGetQuarter()
	{
		$time = Time::parse('April 15, 2015');

		$this->assertEquals(2, $time->quarter);
	}

	public function testGetDST()
	{
		$this->assertFalse(Time::createFromDate(2012, 1, 1)->dst);
		$this->assertTrue(Time::createFromDate(2012, 9, 1)->dst);
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

		$this->assertTrue($instance instanceof \DateTimeZone);
	}

	public function testGetTimezonename()
	{
		$this->assertEquals('America/Chicago', Time::now('America/Chicago')->getTimezoneName());
		$this->assertEquals('Europe/London', Time::now('Europe/London')->getTimezoneName());
	}

	public function testSetYear()
	{
		$time = Time::parse('May 10, 2017');
		$time2 = $time->setYear(2015);

		$this->assertTrue($time2 instanceof Time);
		$this->assertNotSame($time, $time2);
		$this->assertEquals('2015-05-10 00:00:00', $time2->toDateTimeString());
	}

	public function testSetMonthNumber()
	{
		$time = Time::parse('May 10, 2017');
		$time2 = $time->setMonth(4);

		$this->assertTrue($time2 instanceof Time);
		$this->assertNotSame($time, $time2);
		$this->assertEquals('2017-04-10 00:00:00', $time2->toDateTimeString());
	}

	public function testSetMonthLongName()
	{
		$time = Time::parse('May 10, 2017');
		$time2 = $time->setMonth('April');

		$this->assertTrue($time2 instanceof Time);
		$this->assertNotSame($time, $time2);
		$this->assertEquals('2017-04-10 00:00:00', $time2->toDateTimeString());
	}

	public function testSetMonthShortName()
	{
		$time = Time::parse('May 10, 2017');
		$time2 = $time->setMonth('Feb');

		$this->assertTrue($time2 instanceof Time);
		$this->assertNotSame($time, $time2);
		$this->assertEquals('2017-02-10 00:00:00', $time2->toDateTimeString());
	}

	public function testSetDay()
	{
		$time = Time::parse('May 10, 2017');
		$time2 = $time->setDay(15);

		$this->assertTrue($time2 instanceof Time);
		$this->assertNotSame($time, $time2);
		$this->assertEquals('2017-05-15 00:00:00', $time2->toDateTimeString());
	}

	public function testSetHour()
	{
		$time = Time::parse('May 10, 2017');
		$time2 = $time->setHour(15);

		$this->assertTrue($time2 instanceof Time);
		$this->assertNotSame($time, $time2);
		$this->assertEquals('2017-05-10 15:00:00', $time2->toDateTimeString());
	}

	public function testSetMinute()
	{
		$time = Time::parse('May 10, 2017');
		$time2 = $time->setMinute(30);

		$this->assertTrue($time2 instanceof Time);
		$this->assertNotSame($time, $time2);
		$this->assertEquals('2017-05-10 00:30:00', $time2->toDateTimeString());
	}

	public function testSetSecond()
	{
		$time = Time::parse('May 10, 2017');
		$time2 = $time->setSecond(20);

		$this->assertTrue($time2 instanceof Time);
		$this->assertNotSame($time, $time2);
		$this->assertEquals('2017-05-10 00:00:20', $time2->toDateTimeString());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetMonthTooSmall()
	{
		$time = Time::parse('May 10, 2017');
		$time->setMonth(-5);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetMonthTooBig()
	{
		$time = Time::parse('May 10, 2017');
		$time->setMonth(30);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetDayTooSmall()
	{
		$time = Time::parse('May 10, 2017');
		$time->setDay(-5);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetDayTooBig()
	{
		$time = Time::parse('May 10, 2017');
		$time->setDay(80);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetHourTooSmall()
	{
		$time = Time::parse('May 10, 2017');
		$time->setHour(-5);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetHourTooBig()
	{
		$time = Time::parse('May 10, 2017');
		$time->setHour(80);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetMinuteTooSmall()
	{
		$time = Time::parse('May 10, 2017');
		$time->setMinute(-5);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetMinuteTooBig()
	{
		$time = Time::parse('May 10, 2017');
		$time->setMinute(80);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetSecondTooSmall()
	{
		$time = Time::parse('May 10, 2017');
		$time->setSecond(-5);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetSecondTooBig()
	{
		$time = Time::parse('May 10, 2017');
		$time->setSecond(80);
	}

	public function testSetTimezone()
	{
		$time = Time::parse('May 10, 2017', 'America/Chicago');
		$time2 = $time->setTimezone('Europe/London');

		$this->assertTrue($time2 instanceof Time);
		$this->assertNotSame($time, $time2);
		$this->assertEquals('America/Chicago', $time->getTimezoneName());
		$this->assertEquals('Europe/London', $time2->getTimezoneName());
	}

	public function testSetTimestamp()
	{
		$time = Time::parse('May 10, 2017', 'America/Chicago');
		$stamp = strtotime('April 1, 2017');
		$time2 = $time->setTimestamp($stamp);

		$this->assertTrue($time2 instanceof Time);
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
		$time = Time::parse('January 10, 2017', 'America/Chicago');
		$this->assertEquals('Jan 10, 2017', $time->toFormattedDateString());
	}

	public function testToTimeString()
	{
		$time = Time::parse('January 10, 2017 13:20:33', 'America/Chicago');
		$this->assertEquals('13:20:33', $time->toTimeString());
	}
}
