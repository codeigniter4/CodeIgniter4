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

use DateTime;

/**
 * Legacy Time class.
 *
 * This class is only for backward compatibility. Do not use.
 * This is not immutable! Some methods are immutable,
 * but some methods can alter the state.
 *
 * @property string $date
 *
 * @deprecated Use Time instead.
 * @see \CodeIgniter\I18n\TimeLegacyTest
 */
class TimeLegacy extends DateTime
{
    use TimeTrait;
}
