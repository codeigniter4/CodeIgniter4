<?php namespace CodeIgniter\Files\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use RuntimeException;

class FileNotFoundException extends RuntimeException implements ExceptionInterface
{
	public static function forFileNotFound(string $path)
	{
		return new static(lang('Files.fileNotFound', [$path]));
	}
}
