<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Images;

use CodeIgniter\Files\File;
use CodeIgniter\Images\Exceptions\ImageException;

/**
 * Encapsulation of an Image file
 *
 * @see \CodeIgniter\Images\ImageTest
 */
class Image extends File
{
    /**
     * The original image width in pixels.
     *
     * @var float|int
     */
    public $origWidth;

    /**
     * The original image height in pixels.
     *
     * @var float|int
     */
    public $origHeight;

    /**
     * The image type constant.
     *
     * @see http://php.net/manual/en/image.constants.php
     *
     * @var int
     */
    public $imageType;

    /**
     * attributes string with size info:
     * 'height="100" width="200"'
     *
     * @var string
     */
    public $sizeStr;

    /**
     * The image's mime type, i.e. image/jpeg
     *
     * @var string
     */
    public $mime;

    /**
     * Makes a copy of itself to the new location. If no filename is provided
     * it will use the existing filename.
     *
     * @param string      $targetPath The directory to store the file in
     * @param string|null $targetName The new name of the copied file.
     * @param int         $perms      File permissions to be applied after copy.
     */
    public function copy(string $targetPath, ?string $targetName = null, int $perms = 0644): bool
    {
        $targetPath = rtrim($targetPath, '/ ') . '/';

        $targetName ??= $this->getFilename();

        if (empty($targetName)) {
            throw ImageException::forInvalidFile($targetName);
        }

        if (! is_dir($targetPath)) {
            mkdir($targetPath, 0755, true);
        }

        if (! copy($this->getPathname(), "{$targetPath}{$targetName}")) {
            throw ImageException::forCopyError($targetPath);
        }

        chmod("{$targetPath}/{$targetName}", $perms);

        return true;
    }

    /**
     * Get image properties
     *
     * A helper function that gets info about the file
     *
     * @return array|bool
     */
    public function getProperties(bool $return = false)
    {
        $path = $this->getPathname();

        if (! $vals = getimagesize($path)) {
            throw ImageException::forFileNotSupported();
        }

        $types = [
            IMAGETYPE_GIF  => 'gif',
            IMAGETYPE_JPEG => 'jpeg',
            IMAGETYPE_PNG  => 'png',
            IMAGETYPE_WEBP => 'webp',
        ];

        $mime = 'image/' . ($types[$vals[2]] ?? 'jpg');

        if ($return === true) {
            return [
                'width'      => $vals[0],
                'height'     => $vals[1],
                'image_type' => $vals[2],
                'size_str'   => $vals[3],
                'mime_type'  => $mime,
            ];
        }

        $this->origWidth  = $vals[0];
        $this->origHeight = $vals[1];
        $this->imageType  = $vals[2];
        $this->sizeStr    = $vals[3];
        $this->mime       = $mime;

        return true;
    }
}
