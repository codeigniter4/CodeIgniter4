<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Cors extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     *  CORS valid url
     * --------------------------------------------------------------------------
     * It needs to define  valid url to pass cors
     *  for example
     *   Angular Url    http://localhost:4200
     *   React   Url    http://localhost:3000
     *   Domain  Url    https://exmaple.com
     *   Domain  Url    http://exmaple.com
     *  attention no space in array item must exist
     */
    public array $validDomains = ['http://localhost:4200', 'http://localhost:3000'];

    /**
     * --------------------------------------------------------------------------
     *  CORS  Exception Domain
     * --------------------------------------------------------------------------
     *   During development api  with  Postman tools or try access  vis browser at http://localhost/
     *   needs to pass cors
     *   When deploy app to host it needs  to add subdomain too for example  https://api.exmaple.com
     */
    public array $exceptionDomains = ['http://localhost/', '::1'];

    /**
     * --------------------------------------------------------------------------
     *  CORS  valid method
     * --------------------------------------------------------------------------
     * define valid method can  pass cors
     * for example GET POST PUT DELETE OPTIONS
     */
    public array $methods = ['GET', 'PUT', 'POST', 'DELETE', 'PATCH', 'OPTIONS'];

    /**
     * --------------------------------------------------------------------------
     *  CORS  valid header
     * --------------------------------------------------------------------------
     *define valid headers can  pass cors
     * for example Origin Accept Content-Length
     */
    public array $headers = [
        'Origin', 'X-Requested-With', 'Content-Type', 'Accept',
        'Access-Control-Request-Method', 'Access-Control-Allow-Headers',
        'Authorization', 'Content-Length', 'X-Csrf-Token',
    ];

    /**
     * --------------------------------------------------------------------------
     *  CORS   max age
     * --------------------------------------------------------------------------
     * define  max age cors
     */
    public int $maxAge = 3600;

    /**
     * --------------------------------------------------------------------------
     *  CORS  credentials
     * --------------------------------------------------------------------------
     * define  credentials to pass cors
     */
    public bool $credentials = true;

    /**
     * --------------------------------------------------------------------------
     *  CORS  valid  content type
     * --------------------------------------------------------------------------
     * define  content type can pass cors
     */
    public array $contentType = ['application/json', 'charset=utf-8'];
}
