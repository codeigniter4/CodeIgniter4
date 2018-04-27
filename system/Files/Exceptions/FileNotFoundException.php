<?php namespace CodeIgniter\Files\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;

class FileNotFoundException extends \RuntimeException implements ExceptionInterface
{
	public static function forFileNotFound(string $path)
	{
		return new self(lang('Files.fileNotFound', [$path]));
	}
}
