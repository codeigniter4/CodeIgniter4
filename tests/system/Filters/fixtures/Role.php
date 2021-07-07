<?php

namespace CodeIgniter\Filters\fixtures;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Role implements FilterInterface
{
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if (is_array($arguments)) {
            $response->setBody(implode(';', $arguments));
        } elseif ($arguments === null) {
            $response->setBody('Is null');
        } else {
            $response->setBody('Something else');
        }

        return $response;
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        if (is_array($arguments)) {
            return implode(';', $arguments);
        }
        if ($arguments === null) {
            return 'Is null';
        }

        return 'Something else';
    }
}
