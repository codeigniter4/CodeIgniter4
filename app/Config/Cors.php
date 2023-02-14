<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use DateTimeInterface;

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
     *
     */
    public array $validDomains = [''];
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
        "Origin", "X-Requested-With", "Content-Type", "Accept",
        "Access-Control-Request-Method", "Access-Control-Allow-Headers",
        "Authorization", "Content-Length", "X-Csrf-Token"
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
    public string $credentials = "true";
    /**
     * --------------------------------------------------------------------------
     *  CORS  valid  content type
     * --------------------------------------------------------------------------
     * define  content type can pass cors
     */
    public array $contentType = ['application/json', 'charset=utf-8'];


}
