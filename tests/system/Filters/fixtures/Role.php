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
        } elseif (is_null($arguments)) {
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
        if (is_null($arguments)) {
            return 'Is null';
        }

        return 'Something else';
    }
}
