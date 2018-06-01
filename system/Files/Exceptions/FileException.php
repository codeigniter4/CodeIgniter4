<?php namespace CodeIgniter\Files\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;

class FileException extends \RuntimeException implements ExceptionInterface
{

	public static function forUnableToMove(string $from = null, string $to = null, string $error = null)
	{
		return new static(lang('Files.cannotMove', [$from, $to, $error]));
	}

}
