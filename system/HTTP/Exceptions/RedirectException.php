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

namespace CodeIgniter\HTTP\Exceptions;

use CodeIgniter\Exceptions\HTTPExceptionInterface;
use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Exceptions\LogicException;
use CodeIgniter\Exceptions\RuntimeException;
use CodeIgniter\HTTP\ResponsableInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

/**
 * RedirectException
 */
class RedirectException extends RuntimeException implements ExceptionInterface, ResponsableInterface, HTTPExceptionInterface
{
    /**
     * HTTP status code for redirects
     *
     * @var int
     */
    protected $code = 302;

    protected ?ResponseInterface $response = null;

    /**
     * @param ResponseInterface|string $message Response object or a string containing a relative URI.
     * @param int                      $code    HTTP status code to redirect if $message is a string.
     */
    public function __construct($message = '', int $code = 0, ?Throwable $previous = null)
    {
        if (! is_string($message) && ! $message instanceof ResponseInterface) {
            throw new InvalidArgumentException(
                'RedirectException::__construct() first argument must be a string or ResponseInterface',
                0,
                $this,
            );
        }

        if ($message instanceof ResponseInterface) {
            $this->response = $message;
            $message        = '';

            if ($this->response->getHeaderLine('Location') === '' && $this->response->getHeaderLine('Refresh') === '') {
                throw new LogicException(
                    'The Response object passed to RedirectException does not contain a redirect address.',
                );
            }

            if ($this->response->getStatusCode() < 301 || $this->response->getStatusCode() > 308) {
                $this->response->setStatusCode($this->code);
            }
        }

        parent::__construct($message, $code, $previous);
    }

    public function getResponse(): ResponseInterface
    {
        if (! $this->response instanceof ResponseInterface) {
            $this->response = service('response')
                ->redirect(base_url($this->getMessage()), 'auto', $this->getCode());
        }

        service('logger')->info(
            'REDIRECTED ROUTE at '
             . ($this->response->getHeaderLine('Location') ?: substr($this->response->getHeaderLine('Refresh'), 6)),
        );

        return $this->response;
    }
}
