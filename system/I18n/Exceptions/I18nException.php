<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\I18n\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

/**
 * I18nException
 */
class I18nException extends FrameworkException
{
    /**
     * Thrown when createFromFormat fails to receive a valid
     * DateTime back from DateTime::createFromFormat.
     *
     * @param string $format
     *
     * @return static
     */
    public static function forInvalidFormat(string $format)
    {
        return new static(lang('Time.invalidFormat', [$format]));
    }

    /**
     * Thrown when the numeric representation of the month falls
     * outside the range of allowed months.
     *
     * @param string $month
     *
     * @return static
     */
    public static function forInvalidMonth(string $month)
    {
        return new static(lang('Time.invalidMonth', [$month]));
    }

    /**
     * Thrown when the supplied day falls outside the range
     * of allowed days.
     *
     * @param string $day
     *
     * @return static
     */
    public static function forInvalidDay(string $day)
    {
        return new static(lang('Time.invalidDay', [$day]));
    }

    /**
     * Thrown when the day provided falls outside the allowed
     * last day for the given month.
     *
     * @param string $lastDay
     * @param string $day
     *
     * @return static
     */
    public static function forInvalidOverDay(string $lastDay, string $day)
    {
        return new static(lang('Time.invalidOverDay', [$lastDay, $day]));
    }

    /**
     * Thrown when the supplied hour falls outside the
     * range of allowed hours.
     *
     * @param string $hour
     *
     * @return static
     */
    public static function forInvalidHour(string $hour)
    {
        return new static(lang('Time.invalidHour', [$hour]));
    }

    /**
     * Thrown when the supplied minutes falls outside the
     * range of allowed minutes.
     *
     * @param string $minutes
     *
     * @return static
     */
    public static function forInvalidMinutes(string $minutes)
    {
        return new static(lang('Time.invalidMinutes', [$minutes]));
    }

    /**
     * Thrown when the supplied seconds falls outside the
     * range of allowed seconds.
     *
     * @param string $seconds
     *
     * @return static
     */
    public static function forInvalidSeconds(string $seconds)
    {
        return new static(lang('Time.invalidSeconds', [$seconds]));
    }
}
