<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Class CORS
 *
 * Stores the default settings for the CORS, if you choose to use it. The
 * values here will be read in and set as defaults for the site. If needed,
 * they can be overridden on a page-by-page basis.
 *
 * Suggested reference for explanations:
 *   - https://developer.mozilla.org/pt-BR/docs/Web/HTTP/Controle_Acesso_CORS
 *   - https://www.php.net/manual/en/function.header.php
 *
 * @author Marcelo Ratton <www.MarceloRatton.com>
 * @package Config
 */
class CORS extends BaseConfig
{
  /**
   * Header to allow CORS for any domain request
   * Access-Control-Allow-Origin  = * for all domains, if you need to set one
   *    change this value
   * Access-Control-Allow-Methods = POST, GET, OPTIONS, PUT, DELETE - default
   *    methods, for all methods in HTTP: POST, GET, OPTIONS, PUT, DELETE,
   *    PATCH, MATCH, CONNECT, TRACE
   */
  public $headerList = [
    'Access-Control-Allow-Origin'      => '*',
    'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE',
    'Access-Control-Allow-Credentials' => 'true',
    'Access-Control-Max-Age'           => '86400',
    'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With'
];
}
