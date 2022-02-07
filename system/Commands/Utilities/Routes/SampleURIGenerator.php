<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands\Utilities\Routes;

use CodeIgniter\Config\Services;
use CodeIgniter\Router\RouteCollection;

/**
 * Generate a sample URI path from route key regex.
 */
final class SampleURIGenerator
{
    private RouteCollection $routes;

    /**
     * Sample URI path for placeholder.
     *
     * @var array<string, string>
     */
    private array $samples = [
        'any'      => '123/abc',
        'segment'  => 'abc_123',
        'alphanum' => 'abc123',
        'num'      => '123',
        'alpha'    => 'abc',
        'hash'     => 'abc_123',
    ];

    public function __construct(?RouteCollection $routes = null)
    {
        $this->routes = $routes ?? Services::routes();
    }

    /**
     * @param string $routeKey route key regex
     *
     * @return string sample URI path
     */
    public function get(string $routeKey): string
    {
        $sampleUri = $routeKey;

        foreach ($this->routes->getPlaceholders() as $placeholder => $regex) {
            $sample = $this->samples[$placeholder] ?? '::unknown::';

            $sampleUri = str_replace('(' . $regex . ')', $sample, $sampleUri);
        }

        // auto route
        return str_replace('[/...]', '/1/2/3/4/5', $sampleUri);
    }
}
