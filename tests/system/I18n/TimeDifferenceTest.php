<?php
namespace CodeIgniter\I18n;

class TimeDifferenceTest extends \CIUnitTestCase
{

	protected function setUp(): void
	{
		parent::setUp();

		helper('date');
		\Locale::setDefault('America/Chicago');
	}

	public function testDifferenceBasics()
	{
		$current = Time::parse('March 10, 2017', 'America/Chicago');
		$test    = Time::parse('March 10, 2010', 'America/Chicago');

		$diff = $current->getTimestamp() - $test->getTimestamp();

		$obj = $current->difference($test);

		$this->assertEquals(-7, $obj->getYears());
		$this->assertEquals(-84, $obj->getMonths());
		$this->assertEquals(-365, $obj->getWeeks());
		$this->assertEquals(-2557, $obj->getDays());
		$this->assertEquals(-61368, $obj->getHours());
		$this->assertEquals(-3682080, $obj->getMinutes());
		$this->assertEquals(-220924800, $obj->getSeconds());

		$this->assertEquals($diff / YEAR, $obj->getYears(true));
		$this->assertEquals($diff / MONTH, $obj->getMonths(true));
		$this->assertEquals($diff / WEEK, $obj->getWeeks(true));
		$this->assertEquals($diff / DAY, $obj->getDays(true));
		$this->assertEquals($diff / HOUR, $obj->getHours(true));
		$this->assertEquals($diff / MINUTE, $obj->getMinutes(true));
		$this->assertEquals($diff / SECOND, $obj->getSeconds(true));
	}

	public function testHumanizeYearsSingle()
	{
		$current = Time::parse('March 10, 2017', 'America/Chicago');

		$diff = $current->difference('March 9, 2016 12:00:00', 'America/Chicago');

		$this->assertEquals('1 year ago', $diff->humanize('en'));
	}

	public function testHumanizeYearsPlural()
	{
		$current = Time::parse('March 10, 2017', 'America/Chicago');
		$diff    = $current->difference('March 9, 2014 12:00:00', 'America/Chicago');

		$this->assertEquals('3 years ago', $diff->humanize('en'));
	}

	public function testHumanizeYearsForward()
	{
		$current = Time::parse('January 1, 2017', 'America/Chicago');
		$diff    = $current->difference('January 1, 2018 12:00:00', 'America/Chicago');

		$this->assertEquals('in 1 year', $diff->humanize('en'));
	}

	public function testHumanizeMonthsSingle()
	{
		$current = Time::parse('March 10, 2017', 'America/Chicago');
		$diff    = $current->difference('February 9, 2017', 'America/Chicago');

		$this->assertEquals('1 month ago', $diff->humanize('en'));
	}

	public function testHumanizeMonthsPlural()
	{
		$current = Time::parse('March 1, 2017', 'America/Chicago');
		$diff    = $current->difference('January 1, 2017', 'America/Chicago');

		$this->assertEquals('2 months ago', $diff->humanize('en'));
	}

	public function testHumanizeMonthsForward()
	{
		$current = Time::parse('March 1, 2017', 'America/Chicago');
		$diff    = $current->difference('May 1, 2017', 'America/Chicago');

		$this->assertEquals('in 1 month', $diff->humanize('en'));
	}

	public function testHumanizeDaysSingle()
	{
		$current = Time::parse('March 10, 2017', 'America/Chicago');
		$diff    = $current->difference('March 9, 2017', 'America/Chicago');

		$this->assertEquals('1 day ago', $diff->humanize('en'));
	}

	public function testHumanizeDaysPlural()
	{
		$current = Time::parse('March 10, 2017', 'America/Chicago');
		$diff    = $current->difference('March 8, 2017', 'America/Chicago');

		$this->assertEquals('2 days ago', $diff->humanize('en'));
	}

	public function testHumanizeDaysForward()
	{
		$current = Time::parse('March 10, 2017', 'America/Chicago');
		$diff    = $current->difference('March 11, 2017', 'America/Chicago');

		$this->assertEquals('in 1 day', $diff->humanize('en'));
	}

	public function testHumanizeHoursSingle()
	{
		$current = Time::parse('March 10, 2017 12:00', 'America/Chicago');
		$diff    = $current->difference('March 10, 2017 11:00', 'America/Chicago');

		$this->assertEquals('1 hour ago', $diff->humanize('en'));
	}

	public function testHumanizeHoursPlural()
	{
		$current = Time::parse('March 10, 2017 12:00', 'America/Chicago');
		$diff    = $current->difference('March 10, 2017 10:00', 'America/Chicago');

		$this->assertEquals('2 hours ago', $diff->humanize('en'));
	}

	public function testHumanizeHoursForward()
	{
		$current = Time::parse('March 10, 2017 12:00', 'America/Chicago');
		$diff    = $current->difference('March 10, 2017 13:00', 'America/Chicago');

		$this->assertEquals('in 1 hour', $diff->humanize('en'));
	}

	public function testHumanizeMinutesSingle()
	{
		$current = Time::parse('March 10, 2017 12:30', 'America/Chicago');
		$diff    = $current->difference('March 10, 2017 12:29', 'America/Chicago');

		$this->assertEquals('1 minute ago', $diff->humanize('en'));
	}

	public function testHumanizeMinutesPlural()
	{
		$current = Time::parse('March 10, 2017 12:30', 'America/Chicago');
		$diff    = $current->difference('March 10, 2017 12:28', 'America/Chicago');

		$this->assertEquals('2 minutes ago', $diff->humanize('en'));
	}

	public function testHumanizeMinutesForward()
	{
		$current = Time::parse('March 10, 2017 12:30', 'America/Chicago');
		$diff    = $current->difference('March 10, 2017 12:31', 'America/Chicago');

		$this->assertEquals('in 1 minute', $diff->humanize('en'));
	}

	public function testHumanizeWeeksSingle()
	{
		$current = Time::parse('March 10, 2017', 'America/Chicago');
		$diff    = $current->difference('March 2, 2017', 'America/Chicago');

		$this->assertEquals('1 week ago', $diff->humanize('en'));
	}

	public function testHumanizeWeeksPlural()
	{
		$current = Time::parse('March 30, 2017', 'America/Chicago');
		$diff    = $current->difference('March 15, 2017', 'America/Chicago');

		$this->assertEquals('2 weeks ago', $diff->humanize('en'));
	}

	public function testHumanizeWeeksForward()
	{
		$current = Time::parse('March 10, 2017', 'America/Chicago');
		$diff    = $current->difference('March 18, 2017', 'America/Chicago');

		$this->assertEquals('in 1 week', $diff->humanize('en'));
	}

	public function testHumanizeNoDifference()
	{
		$current = Time::parse('March 10, 2017', 'America/Chicago');
		$diff    = $current->difference('March 10, 2017', 'America/Chicago');

		$this->assertEquals('Just now', $diff->humanize('en'));
	}

	public function testGetter()
	{
		$current = Time::parse('March 10, 2017', 'America/Chicago');
		$diff    = $current->difference('March 18, 2017', 'America/Chicago');

		$this->assertEquals(-8, (int) round($diff->days));
		$this->assertNull($diff->nonsense);
	}

	public function testMagicIssetTrue()
	{
		$current = Time::parse('March 10, 2017', 'America/Chicago');
		$diff    = $current->difference('March 18, 2017', 'America/Chicago');

		$this->assertTrue(isset($diff->days));
		$this->assertFalse(isset($diff->nonsense));
	}

	public function testMagicIssetFalse()
	{
		$current = Time::parse('March 10, 2017', 'America/Chicago');
		$diff    = $current->difference('March 18, 2017', 'America/Chicago');

		$this->assertFalse(isset($diff->nonsense));
	}

}
