<?php namespace CodeIgniter\Images;

use CodeIgniter\Files\File;

class Image extends File
{
	/**
	 * The original image width in pixels.
	 *
	 * @var
	 */
	public $origWidth;
	/**
	 * The original image height in pixels.
	 *
	 * @var
	 */
	public $origHeight;
	/**
	 * The image type constant.
	 * @see http://php.net/manual/en/image.constants.php
	 *
	 * @var int
	 */
	public $imageType;

	/**
	 * attributes string with size info:
	 * 'height="100" width="200"'
	 *
	 * @var string
	 */
	public $sizeStr;

	/**
	 * The image's mime type, i.e. image/jpeg
	 *
	 * @var string
	 */
	public $mime;

	/**
	 * Makes a copy of itself to the new location. If no filename is provided
	 * it will use the existing filename.
	 *
	 * @param string      $targetPath   The directory to store the file in
	 * @param string|null $targetName   The new name of the copied file.
	 * @param int         $perms        File permissions to be applied after copy.
	 *
	 * @return bool
	 */
	public function copy(string $targetPath, string $targetName = null, int $perms = 0644)
	{
		$targetPath = rtrim($targetPath, '/ ').'/';

		$targetName = is_null($targetName)
			? $this->getFilename()
			: $targetName;

		if (empty($targetName))
		{
			throw new ImageException('Invalid file name.');
		}

		if (! is_dir($targetPath))
		{
			mkdir($targetName, 0755, true);
		}

		if (! copy($this->getPathname(),"{$targetPath}{$targetName}"))
		{
			throw new ImageException('Unable to copy image to new destination.');
		}

		chmod("{$targetPath}/{$targetName}", $perms);

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Get image properties
	 *
	 * A helper function that gets info about the file
	 *
	 * @param    string
	 * @param    bool
	 *
	 * @return    mixed
	 */
	public function getProperties($return = false)
	{
		$path = $this->getPathname();

		$vals  = getimagesize($path);
		$types = [1 => 'gif', 2 => 'jpeg', 3 => 'png'];
		$mime  = (isset($types[$vals[2]])) ? 'image/'.$types[$vals[2]] : 'image/jpg';

		if ($return === true)
		{
			return [
				'width'      => $vals[0],
				'height'     => $vals[1],
				'image_type' => $vals[2],
				'size_str'   => $vals[3],
				'mime_type'  => $mime,
			];
		}

		$this->origWidth  = $vals[0];
		$this->origHeight = $vals[1];
		$this->imageType  = $vals[2];
		$this->sizeStr    = $vals[3];
		$this->mime       = $mime;

		return true;
	}

	//--------------------------------------------------------------------

}
