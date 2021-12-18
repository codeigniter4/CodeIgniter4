<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Mock;

use CodeIgniter\Email\Email;
use CodeIgniter\Events\Events;

class MockEmail extends Email
{
    /**
     * Value to return from mocked send().
     *
     * @var bool
     */
    public $returnValue = true;

    public function send($autoClear = true)
    {
        if ($this->returnValue) {
            $this->setArchiveValues();

            if ($autoClear) {
                $this->clear();
            }

            Events::trigger('email', $this->archive);
        }

        return $this->returnValue;
    }
}
