<?php namespace CodeIgniter\Images\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

class ImageException extends FrameworkException implements ExceptionInterface
{
	public static function forMissingAngle()
	{
		return new static(lang('Images.rotationAngleRequired'));
	}

	public static function forInvalidDirection(string $dir = null)
	{
		return new static(lang('Images.invalidDirection', [$dir]));
	}

	public static function forEXIFUnsupported()
	{
		return new static(lang('Images.exifNotSupported'));
	}

	public static function forInvalidImageCreate(string $extra = null)
	{
		return new static(lang('Images.unsupportedImagecreate').' '.$extra);
	}

	public static function forSaveFailed()
	{
		return new static(lang('Images.saveFailed'));
	}

	public static function forInvalidImageLibraryPath(string $path = null)
	{
		return new static(lang('Images.libPathInvalid', [$path]));
	}

	public static function forImageProcessFailed()
	{
		return new static(lang('Images.imageProcessFailed'));
	}
}
