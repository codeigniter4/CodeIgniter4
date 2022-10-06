<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Helpers;

use CodeIgniter\Test\CIUnitTestCase;
use DateTimeZone;

/**
 * @internal
 */
final class DateHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('date');
    }

    public function testNowDefault()
    {
        $this->assertCloseEnough(now(), time());  // close enough
    }

    public function testNowSpecific()
    {
        // Chicago should be two hours ahead of Vancouver
        $this->assertCloseEnough(7200, now('America/Chicago') - now('America/Vancouver'));
    }

    public function testTimezoneSelectDefault()
    {
        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL, null);

        $expected = "<select name='timezone' class='custom-select'>\n";

        foreach ($timezones as $timezone) {
            $selected = ($timezone === 'Asia/Jakarta') ? 'selected' : '';
            $expected .= "<option value='{$timezone}' {$selected}>{$timezone}</option>\n";
        }

        $expected .= ("</select>\n");

        $this->assertSame($expected, timezone_select('custom-select', 'Asia/Jakarta'));
    }

    public function testTimezoneSelectSpecific()
    {
        $spesificRegion = DateTimeZone::ASIA;
        $timezones      = DateTimeZone::listIdentifiers($spesificRegion, null);

        $expected = "<select name='timezone' class='custom-select'>\n";

        foreach ($timezones as $timezone) {
            $selected = ($timezone === 'Asia/Jakarta') ? 'selected' : '';
            $expected .= "<option value='{$timezone}' {$selected}>{$timezone}</option>\n";
        }

        $expected .= ("</select>\n");

        $this->assertSame($expected, timezone_select('custom-select', 'Asia/Jakarta', $spesificRegion));
    }

    public function testTimezoneSelectSingle()
    {
        $spesificRegion = DateTimeZone::PER_COUNTRY;
        $country        = 'ID';
        $timezones      = DateTimeZone::listIdentifiers($spesificRegion, $country);

        $expected = "<select name='timezone' class='custom-select'>\n";

        foreach ($timezones as $timezone) {
            $selected = ($timezone === 'Asia/Jakarta') ? 'selected' : '';
            $expected .= "<option value='{$timezone}' {$selected}>{$timezone}</option>\n";
        }

        $expected .= ("</select>\n");

        $this->assertSame($expected, timezone_select('custom-select', 'Asia/Jakarta', $spesificRegion, $country));
    }
}
