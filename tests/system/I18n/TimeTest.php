<?php namespace CodeIgniter\I18n;

use IntlDateFormatter;

class TimeTest extends \CIUnitTestCase
{
	public function setUp()
	{
		parent::setUp();

		helper('date');
	}

	public function testNewTimeNow()
	{
		$time = new Time();

		$formatter = new IntlDateFormatter(
			'en_US',
			IntlDateFormatter::SHORT,
			IntlDateFormatter::SHORT,
			'America/Chicago',  // Default for CodeIgniter
			IntlDateFormatter::GREGORIAN
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
			IntlDateFormatter::GREGORIAN
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
			IntlDateFormatter::GREGORIAN
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
			'Europe/London',  // Default for CodeIgniter
			IntlDateFormatter::GREGORIAN
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
		$time = Time::parse('next Tuesday');
		$time1 = new \DateTime();
		$time1->modify('next Tuesday');

		$this->assertEquals($time->getTimestamp(), $time1->getTimestamp());
	}

	public function testToDateTimeString()
	{
		$time = Time::parse('2017-01-12 00:00');

		$this->assertEquals('1/12/17, 12:00 AM', (string)$time);
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
		$time = Time::createFromDate(2017, 03, 05);

		$this->assertEquals(date('Y-m-d 00:00:00', strtotime('2017-03-05 00:00:00')), $time->toDateTimeString());
	}

	public function testCreateFromDateLocalized()
	{
		$time = Time::createFromDate(2017, 03, 05, 'Europe/London');

		$this->assertEquals(date('Y-m-d 00:00:00', strtotime('2017-03-05 00:00:00')), $time->toDateTimeString());
	}

	public function testCreateFromTime()
	{
		$time = Time::createFromTime(10, 03, 05);

		$this->assertEquals(date('Y-m-d 10:03:05'), $time->toDateTimeString());
	}

	public function testCreateFromTimeEvening()
	{
		$time = Time::createFromTime(20, 03, 05);

		$this->assertEquals(date('Y-m-d 20:03:05'), $time->toDateTimeString());
	}

	public function testCreateFromTimeLocalized()
	{
		$time = Time::createFromTime(10, 03, 05, 'Europe/London');

		$this->assertEquals(date('Y-m-d 10:03:05'), $time->toDateTimeString());
	}
}
