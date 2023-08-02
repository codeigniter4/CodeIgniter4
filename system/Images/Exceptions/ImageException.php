<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Images\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

class ImageException extends FrameworkException implements ExceptionInterface
{
    public static function forMissingImage(): ImageException
    {
        return new static(lang('Images.sourceImageRequired'));
    }

    public static function forFileNotSupported(): ImageException
    {
        return new static(lang('Images.fileNotSupported'));
    }

    public static function forMissingAngle(): ImageException
    {
        return new static(lang('Images.rotationAngleRequired'));
    }

    public static function forInvalidDirection(?string $dir = null): ImageException
    {
        return new static(lang('Images.invalidDirection', [$dir]));
    }

    public static function forInvalidPath(): ImageException
    {
        return new static(lang('Images.invalidPath'));
    }

    public static function forEXIFUnsupported(): ImageException
    {
        return new static(lang('Images.exifNotSupported'));
    }

    public static function forInvalidImageCreate(?string $extra = null): ImageException
    {
        return new static(lang('Images.unsupportedImageCreate') . ' ' . $extra);
    }

    public static function forSaveFailed(): ImageException
    {
        return new static(lang('Images.saveFailed'));
    }

    public static function forInvalidImageLibraryPath(?string $path = null): ImageException
    {
        return new static(lang('Images.libPathInvalid', [$path]));
    }

    public static function forImageProcessFailed(): ImageException
    {
        return new static(lang('Images.imageProcessFailed'));
    }
}
