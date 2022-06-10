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

    public function testLaterDefault()
    {
        $date = date('j-n-Y G:i:s', strtotime('2022-06-09 15:00:00' . '+1 days'));

        sscanf($date, '%d-%d-%d %d:%d:%d', $day, $month, $year, $hour, $minute, $second);

        $laterDate = mktime($hour, $minute, $second, $month, $day, $year);

        $this->assertCloseEnough(later('2022-06-09 15:00:00', 1, 'D', 'Asia/Jakarta'), $laterDate);
    }
}
