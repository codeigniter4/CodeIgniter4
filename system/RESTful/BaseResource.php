<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\RESTful;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseResource extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * @var string|null The model that holding this resource's data
     */
    protected $modelName;

    /**
     * @var object|null The model that holding this resource's data
     */
    protected $model;

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->setModel($this->modelName);
    }

    /**
     * Set or change the model this controller is bound to.
     * Given either the name or the object, determine the other.
     *
     * @param object|string|null $which
     */
    public function setModel($which = null)
    {
        if ($which) {
            $this->model     = is_object($which) ? $which : null;
            $this->modelName = is_object($which) ? null : $which;
        }

        if (empty($this->model) && ! empty($this->modelName) && class_exists($this->modelName)) {
            $this->model = model($this->modelName);
        }

        if (! empty($this->model) && empty($this->modelName)) {
            $this->modelName = get_class($this->model);
        }
    }
}
