<?php namespace CodeIgniter\Filters;

use CodeIgniter\config\Services;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;


class Cors implements FilterInterface
{
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {


    }

    public function before(RequestInterface $request, $arguments = null)
    {

        /*  get origins */
        if (array_key_exists('HTTP_ORIGIN', $_SERVER)) {
            $origin = $_SERVER['HTTP_ORIGIN'];
        } else if (array_key_exists('HTTP_REFERER', $_SERVER)) {
            $origin = $_SERVER['HTTP_REFERER'];
        } else {
            $origin = $_SERVER['REMOTE_ADDR'];
        }

        /*  get cors valid domain in configuration app*/

        $appConfig = config('App');

        /*  check origin is valid*/

        if (in_array($origin, $appConfig->corsValidDomains)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        }
        /*   append others header*/

        header("Access-Control-Allow-Headers: Origin, X-API-KEY, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Access-Control-Allow-Headers, Authorization, observe, enctype, Content-Length, X-Csrf-Token");
        header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE, PATCH, OPTIONS");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Max-Age: 3600");
        header('content-type: application/json; charset=utf-8');

        /*   if request is option  */
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method == "OPTIONS") {
            header("HTTP/1.1 200 OK CORS");
            die();
        }

    }


}
