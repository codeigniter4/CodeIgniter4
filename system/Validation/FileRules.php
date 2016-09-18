<?php namespace CodeIgniter\Validation;

use CodeIgniter\HTTP\RequestInterface;
use Config\Services;

class FileRules
{
    /**
     * Request instance. So we can get access to the files.
     *
     * @var \CodeIgniter\HTTP\RequestInterface
     */
    protected $request;

    //--------------------------------------------------------------------

    public function __construct(RequestInterface $request = null)
    {
        if (is_null($request))
        {
            $request = Services::request();
        }

        $this->request = $request;
    }

    //--------------------------------------------------------------------

    /**
     * Verifies that $name is the name of a valid uploaded file.
     *
     * @param string $name
     *
     * @return bool
     */
    public function uploaded(string $blank = null, string $name, array $data): bool
    {
        $file = $this->request->getFile($name);

        if (is_null($file))
        {
            return false;
        }

        if (ENVIRONMENT == 'testing')
        {
            return $file->getError() === 0;
        }

        return $file->isValid();
    }

    //--------------------------------------------------------------------

}
