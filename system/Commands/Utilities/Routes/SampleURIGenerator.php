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

namespace CodeIgniter\Commands\Utilities\Routes;

use CodeIgniter\Router\RouteCollection;
use Config\App;
use Config\Routing;

/**
 * Generate a sample URI path from route key regex.
 *
 * @see \CodeIgniter\Commands\Utilities\Routes\SampleURIGeneratorTest
 */
final class SampleURIGenerator
{
    /**
     * Placeholder inserted into a sample URI segment when no sample can be
     * resolved. The resulting URI matches no route, so the ``spark routes``
     * command reports that route's filters as ``<unknown>``.
     */
    public const UNKNOWN_SAMPLE = '::unknown::';

    private readonly RouteCollection $routes;
    private readonly Routing $config;
    private readonly PlaceholderSampleGenerator $sampleGenerator;

    /**
     * Built-in sample URI paths for the standard placeholders shipped with
     * ``RouteCollection``.
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

    /**
     * Memoized resolved samples, keyed by placeholder name.
     *
     * @var array<string, string>
     */
    private array $resolvedCache = [];

    public function __construct(?RouteCollection $routes = null, ?Routing $config = null)
    {
        $this->routes          = $routes ?? service('routes');
        $this->config          = $config ?? config(Routing::class);
        $this->sampleGenerator = new PlaceholderSampleGenerator();
    }

    /**
     * @param string $routeKey route key regex
     *
     * @return string sample URI path
     */
    public function get(string $routeKey): string
    {
        $sampleUri = $routeKey;

        if (str_contains($routeKey, '{locale}')) {
            $sampleUri = str_replace(
                '{locale}',
                config(App::class)->defaultLocale,
                $routeKey,
            );
        }

        foreach ($this->routes->getPlaceholders() as $placeholder => $regex) {
            $sample = $this->resolveSample($placeholder, $regex);

            $sampleUri = str_replace('(' . $regex . ')', $sample, $sampleUri);
        }

        // auto route
        return str_replace('[/...]', '/1/2/3/4/5', $sampleUri);
    }

    private function resolveSample(string $placeholder, string $regex): string
    {
        if (isset($this->resolvedCache[$placeholder])) {
            return $this->resolvedCache[$placeholder];
        }

        $sample = $this->configuredSample($placeholder, $regex)
            ?? $this->samples[$placeholder]
            ?? $this->sampleGenerator->generate($regex)
            ?? self::UNKNOWN_SAMPLE;

        $this->resolvedCache[$placeholder] = $sample;

        return $sample;
    }

    /**
     * Returns the configured sample for the placeholder when one is set and it
     * matches the placeholder regex, otherwise ``null`` so resolution falls
     * through to the built-in, auto-generated, or unknown sample.
     */
    private function configuredSample(string $placeholder, string $regex): ?string
    {
        $sample = $this->config->placeholderSamples[$placeholder] ?? null;

        if ($sample === null) {
            return null;
        }

        return @preg_match('#^(?:' . $regex . ')$#', $sample) === 1 ? $sample : null;
    }
}
