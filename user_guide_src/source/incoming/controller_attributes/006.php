<?php

namespace App\Attributes;

use Attribute;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Router\Attributes\RouteAttributeInterface;

/**
 * Custom Header Attribute
 *
 * Adds custom headers to the response. This is useful for:
 * - Adding security headers
 * - Setting API version information
 * - Adding custom metadata to responses
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class AddHeader implements RouteAttributeInterface
{
    /**
     * @param string $name  The header name
     * @param string $value The header value
     */
    public function __construct(
        private readonly string $name,
        private readonly string $value,
    ) {
    }

    /**
     * Called before the controller method executes.
     * Return null to continue to the controller.
     */
    public function before(RequestInterface $request): RequestInterface|ResponseInterface|null
    {
        // We don't need to do anything before the controller runs
        return null;
    }

    /**
     * Called after the controller method executes.
     * Add the custom header to the response.
     */
    public function after(RequestInterface $request, ResponseInterface $response): ?ResponseInterface
    {
        $response->setHeader($this->name, $this->value);

        return $response;
    }
}
