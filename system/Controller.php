<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter;

use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Validation\Exceptions\ValidationException;
use CodeIgniter\Validation\ValidationInterface;
use Config\Services;
use Config\Validation;
use Psr\Log\LoggerInterface;

/**
 * Class Controller
 */
class Controller
{
    /**
     * Helpers that will be automatically loaded on class instantiation.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * Instance of the main Request object.
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * Instance of the main response object.
     *
     * @var ResponseInterface
     */
    protected $response;

    /**
     * Instance of logger to use.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Should enforce HTTPS access for all methods in this controller.
     *
     * @var int Number of seconds to set HSTS header
     */
    protected $forceHTTPS = 0;

    /**
     * Once validation has been run, will hold the Validation instance.
     *
     * @var ValidationInterface
     */
    protected $validator;

    /**
     * Constructor.
     *
     * @return void
     *
     * @throws HTTPException
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        $this->request  = $request;
        $this->response = $response;
        $this->logger   = $logger;

        if ($this->forceHTTPS > 0) {
            $this->forceHTTPS($this->forceHTTPS);
        }

        // Autoload helper files.
        helper($this->helpers);
    }

    /**
     * A convenience method to use when you need to ensure that a single
     * method is reached only via HTTPS. If it isn't, then a redirect
     * will happen back to this method and HSTS header will be sent
     * to have modern browsers transform requests automatically.
     *
     * @param int $duration The number of seconds this link should be
     *                      considered secure for. Only with HSTS header.
     *                      Default value is 1 year.
     *
     * @return void
     *
     * @throws HTTPException
     */
    protected function forceHTTPS(int $duration = 31_536_000)
    {
        force_https($duration, $this->request, $this->response);
    }

    /**
     * How long to cache the current page for.
     *
     * @params int $time time to live in seconds.
     *
     * @return void
     */
    protected function cachePage(int $time)
    {
        Services::responsecache()->setTtl($time);
    }

    /**
     * Handles "auto-loading" helper files.
     *
     * @deprecated Use `helper` function instead of using this method.
     *
     * @codeCoverageIgnore
     *
     * @return void
     */
    protected function loadHelpers()
    {
        if (empty($this->helpers)) {
            return;
        }

        helper($this->helpers);
    }

    /**
     * A shortcut to performing validation on Request data.
     *
     * @param array|string $rules
     * @param array        $messages An array of custom error messages
     */
    protected function validate($rules, array $messages = []): bool
    {
        $this->setValidator($rules, $messages);

        return $this->validator->withRequest($this->request)->run();
    }

    /**
     * A shortcut to performing validation on any input data.
     *
     * @param array        $data     The data to validate
     * @param array|string $rules
     * @param array        $messages An array of custom error messages
     * @param string|null  $dbGroup  The database group to use
     */
    protected function validateData(array $data, $rules, array $messages = [], ?string $dbGroup = null): bool
    {
        $this->setValidator($rules, $messages);

        return $this->validator->run($data, null, $dbGroup);
    }

    /**
     * @param array|string $rules
     */
    private function setValidator($rules, array $messages): void
    {
        $this->validator = Services::validation();

        // If you replace the $rules array with the name of the group
        if (is_string($rules)) {
            $validation = config(Validation::class);

            // If the rule wasn't found in the \Config\Validation, we
            // should throw an exception so the developer can find it.
            if (! isset($validation->{$rules})) {
                throw ValidationException::forRuleNotFound($rules);
            }

            // If no error message is defined, use the error message in the Config\Validation file
            if (! $messages) {
                $errorName = $rules . '_errors';
                $messages  = $validation->{$errorName} ?? [];
            }

            $rules = $validation->{$rules};
        }

        $this->validator->setRules($rules, $messages);
    }
}
