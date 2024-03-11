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

namespace CodeIgniter\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Performance Metrics filter
 */
class PerformanceMetrics implements FilterInterface
{
    /**
     * We don't need to do anything here.
     *
     * @param array|null $arguments
     */
    public function before(RequestInterface $request, $arguments = null)
    {
    }

    /**
     * Replaces the performance metrics.
     *
     * @param array|null $arguments
     *
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $body = $response->getBody();

        if ($body !== null) {
            $benchmark = service('timer');

            $output = str_replace(
                [
                    '{elapsed_time}',
                    '{memory_usage}',
                ],
                [
                    (string) $benchmark->getElapsedTime('total_execution'),
                    number_format(memory_get_peak_usage() / 1024 / 1024, 3),
                ],
                $body
            );

            $response->setBody($output);
        }
    }
}
