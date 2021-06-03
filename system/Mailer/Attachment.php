<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Mailer;

use CodeIgniter\Files\Exceptions\FileNotFoundException;
use CodeIgniter\Files\File;
use RuntimeException;

/**
 * Attachment Class
 *
 * Represents a file attachment.
 */
class Attachment extends File
{
	/**
	 * Whether the disposition should be 'inline' (versus 'attachment')
	 *
	 * @var bool
	 */
	protected $inline;

	/**
	 * The name to use when sending the file. Defaults to the basename.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The current ID to link to this attachment.
	 * E.g. for inline pictures.
	 *
	 * @var string|null
	 */
	protected $contentId;

	/**
	 * Verifies local files and stores the values.
	 *
	 * @param string $path
	 * @param bool $inline      Whether the disposition should be 'inline' (versus 'attachment')
	 * @param string|null $name An alternate name to use
	 *
	 * @throws FileNotFoundException
	 */
	public function __construct(string $path, bool $inline = false, string $name = null)
	{
		parent::__construct($path, strpos($path, '://') === false);

		$this->inline = $inline;
		$this->name   = $name ?? $this->getFilename();
	}

	/**
	 * Whether this Attachment has a Content-ID set.
	 *
	 * @return boolean
	 */
	public function hasContentId(): bool
	{
		return isset($this->contentId);
	}

	/**
	 * Creates and returns a Content-ID
	 * E.g. for inline pictures.
	 *
	 * @param string $filename
	 *
	 * @return string|boolean
	 */
	public function getContentId()
	{
		if (is_null($this->contentId))
		{
			$this->contentId = uniqid($this->name . '@', true);
		}

		return $this->contentId;
	}

	/**
	 * Returns the file content ready for spooling.
	 *
	 * @return string
	 *
	 * @throws RuntimeException For open/access errors
	 */
	public function getContent(): bool
	{
		// Read in the binary data
		$file = $this->openFile('rb');
		$file->rewind();

		$content = $file->fread($file->getSize());
		unset($file);		

		return chunk_split(base64_encode($content));
	}
}
