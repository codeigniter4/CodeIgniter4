<?php

namespace Tests\Support\View;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class SampleClassWithInitController
 *
 * This class is only used to provide a reference point
 * during tests to make sure that things work as expected.
 */
class SampleClassWithInitController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        $this->response = $response;
    }

    public function index()
    {
        return get_class($this->response);
    }
}
