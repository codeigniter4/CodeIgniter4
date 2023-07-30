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

use CodeIgniter\Test\CIUnitTestCase;
use Locale;

/**
 * @internal
 *
 * @group Others
 */
final class TimeDifferenceTest extends CIUnitTestCase
{
    private string $currentLocale;

    protected function setUp(): void
    {
        parent::setUp();

        helper('date');

        $this->currentLocale = Locale::getDefault();
        Locale::setDefault('en-US');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Locale::setDefault($this->currentLocale);
    }

    public function testDifferenceBasics(): void
    {
        $current = Time::parse('March 10, 2017', 'America/Chicago');
        $test    = Time::parse('March 10, 2010', 'America/Chicago');

        $diff = $current->getTimestamp() - $test->getTimestamp();

        $obj = $current->difference($test);

        $this->assertSame(-7, $obj->getYears());
        $this->assertSame(-84, $obj->getMonths());
        $this->assertSame(-365, $obj->getWeeks());
        $this->assertSame(-2557, $obj->getDays());
        $this->assertSame(-61368, $obj->getHours());
        $this->assertSame(-3_682_080, $obj->getMinutes());
        $this->assertSame(-220_924_800, $obj->getSeconds());

        $this->assertSame(-7, $obj->years);
        $this->assertSame(-84, $obj->months);
        $this->assertSame(-365, $obj->weeks);
        $this->assertSame(-2557, $obj->days);
        $this->assertSame(-61368, $obj->hours);
        $this->assertSame(-3_682_080, $obj->minutes);
        $this->assertSame(-220_924_800, $obj->seconds);

        $this->assertSame($diff / YEAR, $obj->getYears(true));
        $this->assertSame($diff / MONTH, $obj->getMonths(true));
        $this->assertSame($diff / WEEK, $obj->getWeeks(true));
        $this->assertSame($diff / DAY, $obj->getDays(true));
        $this->assertSame($diff / HOUR, $obj->getHours(true));
        $this->assertSame($diff / MINUTE, $obj->getMinutes(true));
        $this->assertSame($diff / SECOND, $obj->getSeconds(true));
    }

    public function testHumanizeYearsSingle(): void
    {
        $current = Time::parse('March 10, 2017', 'America/Chicago');

        $diff = $current->difference('March 9, 2016 12:00:00', 'America/Chicago');

        $this->assertSame('1 year ago', $diff->humanize('en'));
    }

    public function testHumanizeYearsPlural(): void
    {
        $current = Time::parse('March 10, 2017', 'America/Chicago');
        $diff    = $current->difference('March 9, 2014 12:00:00', 'America/Chicago');

        $this->assertSame('3 years ago', $diff->humanize('en'));
    }

    public function testHumanizeYearsForward(): void
    {
        $current = Time::parse('January 1, 2017', 'America/Chicago');
        $diff    = $current->difference('January 1, 2018 12:00:00', 'America/Chicago');

        $this->assertSame('in 1 year', $diff->humanize('en'));
    }

    public function testHumanizeMonthsSingle(): void
    {
        $current = Time::parse('March 10, 2017', 'America/Chicago');
        $diff    = $current->difference('February 9, 2017', 'America/Chicago');

        $this->assertSame('1 month ago', $diff->humanize('en'));
    }

    public function testHumanizeMonthsPlural(): void
    {
        $current = Time::parse('March 1, 2017', 'America/Chicago');
        $diff    = $current->difference('January 1, 2017', 'America/Chicago');

        $this->assertSame('2 months ago', $diff->humanize('en'));
    }

    public function testHumanizeMonthsForward(): void
    {
        $current = Time::parse('March 1, 2017', 'America/Chicago');
        $diff    = $current->difference('May 1, 2017', 'America/Chicago');

        $this->assertSame('in 1 month', $diff->humanize('en'));
    }

    public function testHumanizeDaysSingle(): void
    {
        $current = Time::parse('March 10, 2017', 'America/Chicago');
        $diff    = $current->difference('March 9, 2017', 'America/Chicago');

        $this->assertSame('1 day ago', $diff->humanize('en'));
    }

    public function testHumanizeDaysPlural(): void
    {
        $current = Time::parse('March 10, 2017', 'America/Chicago');
        $diff    = $current->difference('March 8, 2017', 'America/Chicago');

        $this->assertSame('2 days ago', $diff->humanize('en'));
    }

    public function testHumanizeDaysForward(): void
    {
        $current = Time::parse('March 10, 2017', 'America/Chicago');
        $diff    = $current->difference('March 11, 2017', 'America/Chicago');

        $this->assertSame('in 1 day', $diff->humanize('en'));
    }

    public function testHumanizeHoursSingle(): void
    {
        $current = Time::parse('March 10, 2017 12:00', 'America/Chicago');
        $diff    = $current->difference('March 10, 2017 11:00', 'America/Chicago');

        $this->assertSame('1 hour ago', $diff->humanize('en'));
    }

    public function testHumanizeHoursPlural(): void
    {
        $current = Time::parse('March 10, 2017 12:00', 'America/Chicago');
        $diff    = $current->difference('March 10, 2017 10:00', 'America/Chicago');

        $this->assertSame('2 hours ago', $diff->humanize('en'));
    }

    public function testHumanizeHoursForward(): void
    {
        $current = Time::parse('March 10, 2017 12:00', 'America/Chicago');
        $diff    = $current->difference('March 10, 2017 13:00', 'America/Chicago');

        $this->assertSame('in 1 hour', $diff->humanize('en'));
    }

    public function testHumanizeMinutesSingle(): void
    {
        $current = Time::parse('March 10, 2017 12:30', 'America/Chicago');
        $diff    = $current->difference('March 10, 2017 12:29', 'America/Chicago');

        $this->assertSame('1 minute ago', $diff->humanize('en'));
    }

    public function testHumanizeMinutesPlural(): void
    {
        $current = Time::parse('March 10, 2017 12:30', 'America/Chicago');
        $diff    = $current->difference('March 10, 2017 12:28', 'America/Chicago');

        $this->assertSame('2 minutes ago', $diff->humanize('en'));
    }

    public function testHumanizeMinutesForward(): void
    {
        $current = Time::parse('March 10, 2017 12:30', 'America/Chicago');
        $diff    = $current->difference('March 10, 2017 12:31', 'America/Chicago');

        $this->assertSame('in 1 minute', $diff->humanize('en'));
    }

    public function testHumanizeWeeksSingle(): void
    {
        $current = Time::parse('March 10, 2017', 'America/Chicago');
        $diff    = $current->difference('March 2, 2017', 'America/Chicago');

        $this->assertSame('1 week ago', $diff->humanize('en'));
    }

    public function testHumanizeWeeksPlural(): void
    {
        $current = Time::parse('March 30, 2017', 'America/Chicago');
        $diff    = $current->difference('March 15, 2017', 'America/Chicago');

        $this->assertSame('2 weeks ago', $diff->humanize('en'));
    }

    public function testHumanizeWeeksForward(): void
    {
        $current = Time::parse('March 10, 2017', 'America/Chicago');
        $diff    = $current->difference('March 18, 2017', 'America/Chicago');

        $this->assertSame('in 1 week', $diff->humanize('en'));
    }

    public function testHumanizeNoDifference(): void
    {
        $current = Time::parse('March 10, 2017', 'America/Chicago');
        $diff    = $current->difference('March 10, 2017', 'America/Chicago');

        $this->assertSame('Just now', $diff->humanize('en'));
    }

    public function testGetterUTC(): void
    {
        $current = Time::parse('March 10, 2017', 'UTC');
        $diff    = $current->difference('March 18, 2017', 'UTC');

        $this->assertSame(8, $diff->getDays());
        $this->assertSame(8, $diff->days);
        $this->assertSame(-8, (int) round($diff->getDays(true)));
        $this->assertNull($diff->nonsense);
    }

    public function testGetterChicagoTime(): void
    {
        $current = Time::parse('March 10, 2017', 'America/Chicago');
        $diff    = $current->difference('March 18, 2017', 'America/Chicago');

        // Daylight Saving Time had begun since Sun, 12 Mar, 02:00.
        $this->assertSame(7, $diff->getDays());
        $this->assertSame(7, $diff->days);

        // The raw value does not take Daylight Saving Time into account.
        $this->assertSame(-8, (int) round($diff->getDays(true)));
        $this->assertNull($diff->nonsense);
    }

    public function testMagicIssetTrue(): void
    {
        $current = Time::parse('March 10, 2017', 'America/Chicago');
        $diff    = $current->difference('March 18, 2017', 'America/Chicago');

        $this->assertTrue(isset($diff->days));
        $this->assertFalse(isset($diff->nonsense));
    }

    public function testMagicIssetFalse(): void
    {
        $current = Time::parse('March 10, 2017', 'America/Chicago');
        $diff    = $current->difference('March 18, 2017', 'America/Chicago');

        $this->assertFalse(isset($diff->nonsense));
    }
}
