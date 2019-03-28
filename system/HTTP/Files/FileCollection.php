<?php


/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 3.0.0
 * @filesource
 */

namespace CodeIgniter\HTTP\Files;

/**
 * Class FileCollection
 *
 * Provides easy access to uploaded files for a request.
 *
 * @package CodeIgniter\HTTP\Files
 */
class FileCollection
{

	/**
	 * An array of UploadedFile instances for any files
	 * uploaded as part of this request.
	 * Populated the first time either files(), file(), or hasFile()
	 * is called.
	 *
	 * @var array|null
	 */
	protected $files;

	//--------------------------------------------------------------------

	/**
	 * Returns an array of all uploaded files that were found.
	 * Each element in the array will be an instance of UploadedFile.
	 * The key of each element will be the client filename.
	 *
	 * @return array|null
	 */
	public function all()
	{
		$this->populateFiles();

		return $this->files;
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to get a single file from the collection of uploaded files.
	 *
	 * @param string $name
	 *
	 * @return UploadedFile|null
	 */
	public function getFile(string $name)
	{
		$this->populateFiles();

		if ($this->hasFile($name))
		{
			if (strpos($name, '.') !== false)
			{
				$name         = explode('.', $name);
				$uploadedFile = $this->getValueDotNotationSyntax($name, $this->files);
				return ($uploadedFile instanceof UploadedFile) ?
					 $uploadedFile : null;
			}

			if (array_key_exists($name, $this->files))
			{
				$uploadedFile = $this->files[$name];
				return  ($uploadedFile instanceof UploadedFile) ?
					$uploadedFile : null;
			}
		}

		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Checks whether an uploaded file with name $fileID exists in
	 * this request.
	 *
	 * @param string $fileID The name of the uploaded file (from the input)
	 *
	 * @return boolean
	 */
	public function hasFile(string $fileID): bool
	{
		$this->populateFiles();

		if (strpos($fileID, '.') !== false)
		{
			$segments = explode('.', $fileID);

			$el = $this->files;

			foreach ($segments as $segment)
			{
				if (! array_key_exists($segment, $el))
				{
					return false;
				}

				$el = $el[$segment];
			}

			return true;
		}

		return isset($this->files[$fileID]);
	}

	//--------------------------------------------------------------------

	/**
	 * Taking information from the $_FILES array, it creates an instance
	 * of UploadedFile for each one, saving the results to this->files.
	 *
	 * Called by files(), file(), and hasFile()
	 */
	protected function populateFiles()
	{
		if (is_array($this->files))
		{
			return;
		}

		$this->files = [];

		if (empty($_FILES))
		{
			return;
		}

		$files = $this->fixFilesArray($_FILES);

		foreach ($files as $name => $file)
		{
			$this->files[$name] = $this->createFileObject($file);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Given a file array, will create UploadedFile instances. Will
	 * loop over an array and create objects for each.
	 *
	 * @param array $array
	 *
	 * @return array|UploadedFile
	 */
	protected function createFileObject(array $array)
	{
		if (! isset($array['name']))
		{
			$output = [];

			foreach ($array as $key => $values)
			{
				if (! is_array($values))
				{
					continue;
				}

				$output[$key] = $this->createFileObject($values);
			}

			return $output;
		}

		return new UploadedFile(
				$array['tmp_name'] ?? null, $array['name'] ?? null, $array['type'] ?? null, $array['size'] ?? null, $array['error'] ?? null
		);
	}

	//--------------------------------------------------------------------

	/**
	 * Reformats the odd $_FILES array into something much more like
	 * we would expect, with each object having its own array.
	 *
	 * Thanks to Jack Sleight on the PHP Manual page for the basis
	 * of this method.
	 *
	 * @see http://php.net/manual/en/reserved.variables.files.php#118294
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected function fixFilesArray(array $data): array
	{
		$output = [];

		foreach ($data as $name => $array)
		{
			foreach ($array as $field => $value)
			{
				$pointer = &$output[$name];

				if (! is_array($value))
				{
					$pointer[$field] = $value;
					continue;
				}

				$stack    = [&$pointer];
				$iterator = new \RecursiveIteratorIterator(
						new \RecursiveArrayIterator($value), \RecursiveIteratorIterator::SELF_FIRST
				);

				foreach ($iterator as $key => $val)
				{
					array_splice($stack, $iterator->getDepth() + 1);
					$pointer = &$stack[count($stack) - 1];
					$pointer = &$pointer[$key];
					$stack[] = &$pointer;
					if (! $iterator->hasChildren())
					{
						$pointer[$field] = $val;
					}
				}
			}
		}

		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Navigate through a array looking for a particular index
	 *
	 * @param array $index The index sequence we are navigating down
	 * @param array $value The portion of the array to process
	 *
	 * @return mixed
	 */
	protected function getValueDotNotationSyntax(array $index, array $value)
	{
		if (is_array($index) && ! empty($index))
		{
			$current_index = array_shift($index);
		}
		if (is_array($index) && $index && is_array($value[$current_index]) && $value[$current_index])
		{
			return $this->getValueDotNotationSyntax($index, $value[$current_index]);
		}

		return (isset($value[$current_index])) ? $value[$current_index] : null;
	}

}
