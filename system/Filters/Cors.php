<?php namespace CodeIgniter\Filters;


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
        if (array_key_exists('HTTP_ORIGIN', $_SERVER)) {
            //get origin when it is using  front end framework angular or react
            $origin = $_SERVER['HTTP_ORIGIN'];
        } else if (array_key_exists('HTTP_REFERER', $_SERVER)) {
            //  get origin when it is not using  front end  only browser
            $origin = $_SERVER['HTTP_REFERER'];
        } else {
            // get origin when it is  using Postman
            $origin = $_SERVER['REMOTE_ADDR'];
        }

            // get  cors configuration
        $corsConfig = config('Cors');

        //  if origin is not in list , origin header no back in response object  and front end  framework get cors error
        if (in_array($origin, $corsConfig->validDomains)) {
            header('Access-Control-Allow-Origin: ' . $origin);

        }

        //  append  allow headers
        header("Access-Control-Allow-Headers: " . implode(", ", $corsConfig->headers));
        //  append  allow methods
        header("Access-Control-Allow-Methods: " . implode(", ", $corsConfig->methods));
       // append set credentials status
        header("Access-Control-Allow-Credentials: {$corsConfig->credentials}");
        // append set  max age
        header("Access-Control-Max-Age: {$corsConfig->maxAge}");
        // append content type
        header('content-type: ' . implode("; ", $corsConfig->contentType));


        // if request is option call back to browser 200 ok
        if ($request->getMethod() == "options") {
            header("HTTP/1.1 200 OK CORS");

        }

    }


}
