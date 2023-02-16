<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Filters;

use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Cors implements FilterInterface
{
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        // get origin
        if ($request->getServer('HTTP_ORIGIN')) {
            // get origin when it is using  front end framework angular or React
            // try access  via main domain https://example.com
            $origin = $request->getServer('HTTP_ORIGIN');
        } elseif ($request->getServer('HTTP_REFERER')) {
            // try access back-end end points  subdomain via browser at  https://api.example.com
            $origin = $request->getServer('HTTP_REFERER');
        } else {
            //  during developing api  access  back-end points subdomain via https://api.example.com
            // local machine  by using PostMan
            $origin = $request->getServer('REMOTE_ADDR');
        }

        // get  cors configuration
        $corsConfig = config('Cors');
        $response   = Services::response();
        $mergeArray = array_merge($corsConfig->validDomains, $corsConfig->exceptionDomains);

        //  if origin is not in list , origin header no back in response object  and front end  framework get cors error
        if (in_array($origin, $mergeArray, true)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        } else {
            return $response->setStatusCode(ResponseInterface::HTTP_NOT_ACCEPTABLE, 'Not Allow Domain');
        }

        //  append  allow headers
        header('Access-Control-Allow-Headers: ' . implode(', ', $corsConfig->headers));
        //  append  allow methods
        header('Access-Control-Allow-Methods: ' . implode(', ', $corsConfig->methods));
        // append set credentials status
        header("Access-Control-Allow-Credentials: {$corsConfig->credentials}");
        // append set  max age
        header("Access-Control-Max-Age: {$corsConfig->maxAge}");
        // append content type
        header('content-type: ' . implode('; ', $corsConfig->contentType));

        // if request is option call back to browser 200 ok
        // otherwise  can pass cors because is  exception or method is not option
        if ($request->getMethod() === 'options') {
            $response->setStatusCode(ResponseInterface::HTTP_OK, 'CORS PASS');
        }
    }
}
