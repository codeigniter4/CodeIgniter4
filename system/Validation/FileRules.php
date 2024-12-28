<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Validation;

use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use Config\Mimes;
use InvalidArgumentException;

/**
 * File validation rules
 *
 * @see \CodeIgniter\Validation\FileRulesTest
 */
class FileRules
{
    /**
     * Request instance. So we can get access to the files.
     *
     * @var IncomingRequest
     */
    protected $request;

    /**
     * Constructor.
     */
    public function __construct(?RequestInterface $request = null)
    {
        if (! $request instanceof RequestInterface) {
            $request = service('request');
        }

        assert($request instanceof IncomingRequest || $request instanceof CLIRequest);

        $this->request = $request;
    }

    /**
     * Verifies that $name is the name of a valid uploaded file.
     */
    public function uploaded(?string $blank, string $name): bool
    {
        $files = $this->request->getFileMultiple($name);

        if ($files === [] || $files === null) {
            $files = [$this->request->getFile($name)];
        }

        foreach ($files as $file) {
            if ($file === null) {
                return false;
            }

            if (ENVIRONMENT === 'testing') {
                if ($file->getError() !== 0) {
                    return false;
                }
            } else {
                // Note: cannot unit test this; no way to over-ride ENVIRONMENT?
                // @codeCoverageIgnoreStart
                if (! $file->isValid()) {
                    return false;
                }
                // @codeCoverageIgnoreEnd
            }
        }

        return true;
    }

    /**
     * Verifies if the file's size in Kilobytes is no larger than the parameter.
     */
    public function max_size(?string $blank, string $params): bool
    {
        // Grab the file name off the top of the $params
        // after we split it.
        $paramArray = explode(',', $params);

        if (count($paramArray) !== 2) {
            throw new InvalidArgumentException('Invalid max_size parameter: "' . $params . '"');
        }

        $name  = array_shift($paramArray);
        $files = $this->request->getFileMultiple($name);

        if ($files === [] || $files === null) {
            $files = [$this->request->getFile($name)];
        }

        foreach ($files as $file) {
            if ($file === null) {
                return false;
            }

            if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                return true;
            }

            if ($file->getError() === UPLOAD_ERR_INI_SIZE) {
                return false;
            }

            if ($file->getSize() / 1024 > $paramArray[0]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Uses the mime config file to determine if a file is considered an "image",
     * which for our purposes basically means that it's a raster image or svg.
     */
    public function is_image(?string $blank, string $params): bool
    {
        // Grab the file name off the top of the $params
        // after we split it.
        $params = explode(',', $params);
        $name   = array_shift($params);
        $files  = $this->request->getFileMultiple($name);

        if ($files === [] || $files === null) {
            $files = [$this->request->getFile($name)];
        }

        foreach ($files as $file) {
            if ($file === null) {
                return false;
            }

            if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                return true;
            }

            // We know that our mimes list always has the first mime
            // start with `image` even when then are multiple accepted types.
            $type = Mimes::guessTypeFromExtension($file->getExtension()) ?? '';

            if (mb_strpos($type, 'image') !== 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks to see if an uploaded file's mime type matches one in the parameter.
     */
    public function mime_in(?string $blank, string $params): bool
    {
        // Grab the file name off the top of the $params
        // after we split it.
        $params = explode(',', $params);
        $name   = array_shift($params);
        $files  = $this->request->getFileMultiple($name);

        if ($files === [] || $files === null) {
            $files = [$this->request->getFile($name)];
        }

        foreach ($files as $file) {
            if ($file === null) {
                return false;
            }

            if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                return true;
            }

            if (! in_array($file->getMimeType(), $params, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks to see if an uploaded file's extension matches one in the parameter.
     */
    public function ext_in(?string $blank, string $params): bool
    {
        // Grab the file name off the top of the $params
        // after we split it.
        $params = explode(',', $params);
        $name   = array_shift($params);
        $files  = $this->request->getFileMultiple($name);

        if ($files === [] || $files === null) {
            $files = [$this->request->getFile($name)];
        }

        foreach ($files as $file) {
            if ($file === null) {
                return false;
            }

            if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                return true;
            }

            if (! in_array($file->guessExtension(), $params, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks an uploaded file to verify that the dimensions are within
     * a specified allowable dimension.
     */
    public function max_dims(?string $blank, string $params): bool
    {
        // Grab the file name off the top of the $params
        // after we split it.
        $params = explode(',', $params);
        $name   = array_shift($params);
        $files  = $this->request->getFileMultiple($name);

        if ($files === [] || $files === null) {
            $files = [$this->request->getFile($name)];
        }

        foreach ($files as $file) {
            if ($file === null) {
                return false;
            }

            if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                return true;
            }

            // Get Parameter sizes
            $allowedWidth  = $params[0] ?? 0;
            $allowedHeight = $params[1] ?? 0;

            // Get uploaded image size
            $info = getimagesize($file->getTempName());

            if ($info === false) {
                // Cannot get the image size.
                return false;
            }

            $fileWidth  = $info[0];
            $fileHeight = $info[1];

            if ($fileWidth > $allowedWidth || $fileHeight > $allowedHeight) {
                return false;
            }
        }

        return true;
    }
}
