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

use DateTime;
use Exception;
use ReturnTypeWillChange;

/**
 * Legacy Time class.
 *
 * This class is only for backward compatibility. Do not use.
 * This is not immutable! Some methods are immutable,
 * but some methods can alter the state.
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
 * @phpstan-consistent-constructor
 *
 * @deprecated Use Time instead.
 * @see \CodeIgniter\I18n\TimeLegacyTest
 */
class TimeLegacy extends DateTime
{
    use TimeTrait;

    /**
     * Returns a new instance with the date set to the new timestamp.
     *
     * @param int $timestamp
     *
     * @return static
     *
     * @throws Exception
     */
    #[ReturnTypeWillChange]
    public function setTimestamp($timestamp)
    {
        $time = date('Y-m-d H:i:s', $timestamp);

        return static::parse($time, $this->timezone, $this->locale);
    }
}
