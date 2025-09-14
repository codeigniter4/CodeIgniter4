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

namespace CodeIgniter\Honeypot\Exceptions;

use CodeIgniter\Exceptions\ConfigException;

class HoneypotException extends ConfigException
{
    /**
     * Thrown when the template value of config is empty.
     *
     * @return static
     */
    public static function forNoTemplate()
    {
        return new static(lang('Honeypot.noTemplate'));
    }

    /**
     * Thrown when the name value of config is empty.
     *
     * @return static
     */
    public static function forNoNameField()
    {
        return new static(lang('Honeypot.noNameField'));
    }

    /**
     * Thrown when the hidden value of config is false.
     *
     * @return static
     *
     * @deprecated 4.6.4 Never used.
     */
    public static function forNoHiddenValue()
    {
        return new static(lang('Honeypot.noHiddenValue'));
    }

    /**
     * Thrown when there are no data in the request of honeypot field.
     *
     * @return static
     */
    public static function isBot()
    {
        return new static(lang('Honeypot.theClientIsABot'));
    }
}
