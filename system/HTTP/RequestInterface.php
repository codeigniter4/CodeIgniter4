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

namespace CodeIgniter\HTTP;

/**
 * Representation of an incoming, server-side HTTP request.
 *
 * Corresponds to Psr7\ServerRequestInterface.
 */
interface RequestInterface extends OutgoingRequestInterface
{
    /**
     * Gets the user's IP address.
     * Supplied by RequestTrait.
     *
     * @return string IP address
     */
    public function getIPAddress(): string;

    /**
     * Fetch an item from the $_SERVER array.
     * Supplied by RequestTrait.
     *
     * @param array|string|null $index  Index for item to be fetched from $_SERVER
     * @param int|null          $filter A filter name to be applied
     *
     * @return mixed
     */
    public function getServer($index = null, $filter = null);
}
