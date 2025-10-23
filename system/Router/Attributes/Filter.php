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

namespace CodeIgniter\Router\Attributes;

use Attribute;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filter Attribute
 *
 * Applies CodeIgniter filters to controller classes or methods. Filters can perform
 * operations before or after controller execution, such as authentication, CSRF protection,
 * rate limiting, or request/response manipulation.
 *
 * Limitations:
 * - Filter must be registered in Config\Filters.php or won't be found
 * - Does not validate filter existence at attribute definition time
 * - Cannot conditionally apply filters based on runtime conditions
 * - Class-level filters cannot be overridden or disabled for specific methods
 *
 * Security Considerations:
 * - Filters run in the order specified; authentication should typically come first
 * - Don't rely solely on filters for critical security; validate in controllers too
 * - Ensure sensitive filters are registered as globals if they should apply site-wide
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Filter implements RouteAttributeInterface
{
    public function __construct(
        public string $by,
        public array $having = [],
    ) {
    }

    public function before(RequestInterface $request): RequestInterface|ResponseInterface|null
    {
        // Filters are handled by the filter system via getFilters()
        // No processing needed here
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response): ?ResponseInterface
    {
        return null;
    }

    public function getFilters(): array
    {
        if ($this->having === []) {
            return [$this->by];
        }

        return [$this->by . ':' . implode(',', $this->having)];
    }
}
