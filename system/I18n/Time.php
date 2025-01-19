<?php

declare(strict_types=1);

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
use Stringable;

/**
 * A localized date/time package inspired
 * by Nesbot/Carbon and CakePHP/Chronos.
 *
 * Requires the intl PHP extension.
 *
 * @property-read int    $age
 * @property-read string $day
 * @property-read string $dayOfWeek
 * @property-read string $dayOfYear
 * @property-read bool   $dst
 * @property-read string $hour
 * @property-read bool   $local
 * @property-read string $minute
 * @property-read string $month
 * @property-read string $quarter
 * @property-read string $second
 * @property-read int    $timestamp
 * @property-read bool   $utc
 * @property-read string $weekOfMonth
 * @property-read string $weekOfYear
 * @property-read string $year
 *
 * @phpstan-consistent-constructor
 *
 * @see \CodeIgniter\I18n\TimeTest
 */
class Time extends DateTimeImmutable implements Stringable
{
    use TimeTrait;
}
