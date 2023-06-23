<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP\Exceptions;

use CodeIgniter\Exceptions\HTTPExceptionInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Exception;
use InvalidArgumentException;
use Throwable;

/**
 * RedirectException
 */
class RedirectException extends Exception implements ResponsableInterface, HTTPExceptionInterface
{
    /**
     * HTTP status code for redirects
     *
     * @var int
     */
    protected $code = 302;

    protected ?ResponseInterface $response = null;

    /**
     * @param ResponseInterface|string $message
     */
    public function __construct($message = '', int $code = 0, ?Throwable $previous = null)
    {
        if (! is_string($message) && ! $message instanceof ResponseInterface) {
            throw new InvalidArgumentException(
                'RedirectException::__construct() first argument must be a string or ResponseInterface',
                0,
                $this
            );
        }

        if ($message instanceof ResponseInterface) {
            $this->response = $message;
            $message        = '';
        }

        parent::__construct($message, $code, $previous);
    }

    public function getResponse(): ResponseInterface
    {
        if (null !== $this->response) {
            return $this->response;
        }

        $logger   = Services::logger();
        $response = Services::response();
        $logger->info('REDIRECTED ROUTE at ' . $this->getMessage());

        // If the route is a 'redirect' route, it throws
        // the exception with the $to as the message
        return $response->redirect(base_url($this->getMessage()), 'auto', $this->getCode());
    }
}
