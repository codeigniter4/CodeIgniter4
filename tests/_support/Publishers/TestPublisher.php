<?php

namespace Tests\Support\Publishers;

use CodeIgniter\Publisher\Publisher;
use RuntimeException;

final class TestPublisher extends Publisher
{
	/**
	 * Fakes an error on the given file.
	 *
	 * @return $this
	 */
	public static function setResult(bool $result)
	{
		self::$result = $result;
	}

	/**
	 * Return value for publish()
	 *
	 * @var boolean
	 */
	private static $result = true;

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
		$this->addPath('');

		return self::$result;
	}
}
