<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

// Cookie language settings
return [
    'invalidExpiresTime'    => 'Invalid "{0}" type for "Expires" attribute. Expected: string, integer, DateTimeInterface object.',
    'invalidExpiresValue'   => 'The cookie expiration time is not valid.',
    'invalidCookieName'     => 'The cookie name "{0}" contains invalid characters.',
    'emptyCookieName'       => 'The cookie name cannot be empty.',
    'invalidSecurePrefix'   => 'Using the "__Secure-" prefix requires setting the "Secure" attribute.',
    'invalidHostPrefix'     => 'Using the "__Host-" prefix must be set with the "Secure" flag, must not have a "Domain" attribute, and the "Path" is set to "/".',
    'invalidSameSite'       => 'The SameSite value must be None, Lax, Strict or a blank string, {0} given.',
    'invalidSameSiteNone'   => 'Using the "SameSite=None" attribute requires setting the "Secure" attribute.',
    'invalidCookieInstance' => '"{0}" class expected cookies array to be instances of "{1}" but got "{2}" at index {3}.',
    'unknownCookieInstance' => 'Cookie object with name "{0}" and prefix "{1}" was not found in the collection.',
];
