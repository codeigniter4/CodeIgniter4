<?php namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use CodeIgniter\Honeypot\Exceptions\HoneypotException;

class Honeypot implements FilterInterface 
{

    /**
	 * Checks if Honeypot field is empty, if so
     * then the requester is a bot,show a blank
     * page
	 *
	 * @param RequestInterface|\CodeIgniter\HTTP\IncomingRequest $request
	 *
	 * @return mixed
	 */

    public function before (RequestInterface $request) 
    {

        // Checks honeypot field if value was entered then show blank if so.
    
        $honeypot = Services::honeypot(new \Config\Honeypot());
        if($honeypot->hasContent($request))
        {
            throw HoneypotException::isBot();
        }
        
    }

    /**
	 * Checks if Honeypot field is empty, if so
     * then the requester is a bot,show a blank
     * page
	 *
	 * @param RequestInterface|\CodeIgniter\HTTP\IncomingRequest $request
	 * @param ResponseInterface|\CodeIgniter\HTTP\Response $response
	 * @return mixed
	 */

    public function after (RequestInterface $request, ResponseInterface $response) 
    {
        
        $honeypot = Services::honeypot(new \Config\Honeypot());
        $honeypot->attachHoneypot($response);
    }
}
