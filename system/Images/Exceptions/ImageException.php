<?php namespace CodeIgniter\Images\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

class ImageException extends FrameworkException implements ExceptionInterface
{
	public static function forMissingAngle()
	{
		return new self(lang('Images.rotationAngleRequired'));
	}

	public static function forInvalidDirection(string $dir = null)
	{
		return new self(lang('Images.invalidDirection', [$dir]));
	}

	public static function forEXIFUnsupported()
	{
		return new self(lang('Images.exifNotSupported'));
	}

	public static function forInvalidImageCreate(string $extra = null)
	{
		return new self(lang('Images.unsupportedImagecreate').' '.$extra);
	}

	public static function forSaveFailed()
	{
		return new self(lang('Images.saveFailed'));
	}

	public static function forInvalidImageLibraryPath(string $path = null)
	{
		return new self(lang('Images.libPathInvalid', [$path]));
	}

	public static function forImageProcessFailed()
	{
		return new self(lang('Images.imageProcessFailed'));
	}
}
