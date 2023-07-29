<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Utilities\Routes\AutoRouterImproved\Controllers;

use CodeIgniter\Controller;

/**
 * The default controller for Auto Routing (Improved)
 */
class Home extends Controller
{
    public function getIndex(): void
    {
    }

    public function postIndex(): void
    {
    }

    /**
     * This method cannot be accessible.
     */
    public function getFoo(): void
    {
    }
}
