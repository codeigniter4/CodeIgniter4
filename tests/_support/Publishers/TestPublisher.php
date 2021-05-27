<?php

namespace Tests\Support\Publishers;

use CodeIgniter\Publisher\Publisher;

final class TestPublisher extends Publisher
{
	/**
	 * Fakes an error on the given file.
	 *
	 * @return $this
	 */
	public static function setError(string $file)
	{
		self::$error = $file;
	}

	/**
	 * A file to cause an error
	 *
	 * @var string
	 */
	private static $error = '';

	/**
	 * Base path to use for the source.
	 *
	 * @var string
	 */
	protected $source = SUPPORTPATH . 'Files';

	/**
	 * Base path to use for the destination.
	 *
	 * @var string
	 */
	protected $destination = WRITEPATH;

	/**
	 * Fakes a publish event so no files are actually copied.
	 */
	public function publish(): bool
	{
		$this->errors = $this->published = [];

		$this->addPath('');

		// Copy each sourced file to its relative destination
		foreach ($this->getFiles() as $file)
		{
			if ($file === self::$error)
			{
				$this->errors[$file] = new RuntimeException('Have an error, dear.');
			}
			else
			{
				// Resolve the destination path
				$this->published[] = $this->destination . substr($file, strlen($this->source));
			}
		}

		return $this->errors === [];
	}
}
