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

    /**
     * Verifies if the file's size in Kilobytes is larger than the parameter.
     *
     * @param string|null $blank
     * @param string      $params
     * @param array       $data
     *
     * @return bool
     */
    public function max_size(string $blank = null, string $params, array $data): bool
    {
        // Grab the file name off the top of the $params
        // after we split it.
        $params = explode(',', $params);
        $name  = array_shift($params);

        $file = $this->request->getFile($name);

        if (is_null($file))
        {
            return false;
        }

        return $params[0] > $file->getSize('kb');
    }

    //--------------------------------------------------------------------

    /**
     * Uses the mime config file to determine if a file is considered an "image",
     * which for our purposes basically means that it's a raster image or svg.
     *
     * @param string|null $blank
     * @param string      $params
     * @param array       $data
     *
     * @return bool
     */
    public function is_image(string $blank = null, string $params, array $data): bool
    {
        // Grab the file name off the top of the $params
        // after we split it.
        $params = explode(',', $params);
        $name  = array_shift($params);

        $file = $this->request->getFile($name);

        if (is_null($file))
        {
            return false;
        }

        // We know that our mimes list always has the first mime
        // start with `image` even when then are multiple accepted types.
        $type = \Config\Mimes::guessTypeFromExtension($file->getExtension());

        return mb_strpos($type, 'image') === 0;
    }

    //--------------------------------------------------------------------

    /**
     * Checks to see if an uploaded file's mime type matches one in the parameter.
     *
     * @param string|null $blank
     * @param string      $params
     * @param array       $data
     *
     * @return bool
     */
    public function mime_in(string $blank = null, string $params, array $data): bool
    {
        // Grab the file name off the top of the $params
        // after we split it.
        $params = explode(',', $params);
        $name  = array_shift($params);

        $file = $this->request->getFile($name);

        if (is_null($file))
        {
            return false;
        }

        return in_array($file->getType(), $params);
    }

    //--------------------------------------------------------------------

    /**
     * Checks to see if an uploaded file's extension matches one in the parameter.
     *
     * @param string|null $blank
     * @param string      $params
     * @param array       $data
     *
     * @return bool
     */
    public function ext_in(string $blank = null, string $params, array $data): bool
    {
        // Grab the file name off the top of the $params
        // after we split it.
        $params = explode(',', $params);
        $name  = array_shift($params);

        $file = $this->request->getFile($name);

        if (is_null($file))
        {
            return false;
        }

        return in_array($file->getExtension(), $params);
    }

    //--------------------------------------------------------------------

    /**
     * Checks an uploaded file to verify that the dimensions are within
     * a specified allowable dimension.
     *
     * @param string|null $blank
     * @param string      $params
     * @param array       $data
     *
     * @return bool
     */
    public function max_dims(string $blank = null, string $params, array $data): bool
    {
        // Grab the file name off the top of the $params
        // after we split it.
        $params = explode(',', $params);
        $name  = array_shift($params);

        $file = $this->request->getFile($name);

        if (is_null($file))
        {
            return false;
        }

        // Get Parameter sizes
        $allowedWidth  = $params[0] ?? 0;
        $allowedHeight = $params[1] ?? 0;

        // Get uploaded image size
        $info = getimagesize($file->getTempName());
        $fileWidth = $info[0];
        $fileHeight = $info[1];

        return $fileWidth <= $allowedWidth && $fileHeight <= $allowedHeight;
    }

    //--------------------------------------------------------------------

}
