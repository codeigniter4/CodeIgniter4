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

namespace Tests\Support\HTTP\Responses;

use CodeIgniter\HTTP\Response;

class ResponseWithPostSendFlag extends Response
{
    public $responseSent = false;

    /**
     * Sends the output to the browser.
     *
     * @return $this
     */
    public function send()
    {
        parent::send();

        $this->responseSent = true;

        return $this;
    }
}
