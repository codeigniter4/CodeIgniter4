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

use DateTimeImmutable;

/**
 * A localized date/time package inspired
 * by Nesbot/Carbon and CakePHP/Chronos.
 *
 * Requires the intl PHP extension.
 *
 * @property int    $age         read-only
 * @property string $day         read-only
 * @property string $dayOfWeek   read-only
 * @property string $dayOfYear   read-only
 * @property bool   $dst         read-only
 * @property string $hour        read-only
 * @property bool   $local       read-only
 * @property string $minute      read-only
 * @property string $month       read-only
 * @property string $quarter     read-only
 * @property string $second      read-only
 * @property int    $timestamp   read-only
 * @property bool   $utc         read-only
 * @property string $weekOfMonth read-only
 * @property string $weekOfYear  read-only
 * @property string $year        read-only
 *
 * @see \CodeIgniter\I18n\TimeTest
 */
class Time extends DateTimeImmutable
{
    use TimeTrait;
}
