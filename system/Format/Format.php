<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Format;

use CodeIgniter\Format\Exceptions\FormatException;
use Config\Format as FormatConfig;

/**
 * The Format class is a convenient place to create Formatters.
 */
class Format
{
	/**
	 * Configuration instance
	 *
	 * @var FormatConfig
	 */
	protected $config;

	/**
	 * Constructor.
	 *
	 * @param FormatConfig $config
	 */
	public function __construct(FormatConfig $config)
	{
		$this->config = $config;
	}

	/**
	 * Returns the current configuration instance.
	 *
	 * @return FormatConfig
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * A Factory method to return the appropriate formatter for the given mime type.
	 *
	 * @param string $mime
	 *
	 * @throws FormatException
	 *
	 * @return FormatterInterface
	 */
	public function getFormatter(string $mime): FormatterInterface
	{
		if (! array_key_exists($mime, $this->config->formatters))
		{
			throw FormatException::forInvalidMime($mime);
		}

		$className = $this->config->formatters[$mime];

		if (! class_exists($className))
		{
			throw FormatException::forInvalidFormatter($className);
		}

		$class = new $className();

		if (! $class instanceof FormatterInterface)
		{
			throw FormatException::forInvalidFormatter($className);
		}

		return $class;
	}
}
