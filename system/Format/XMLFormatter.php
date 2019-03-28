<?php

use CodeIgniter\Format\Exceptions\FormatException;

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
namespace CodeIgniter\Format;

/**
 * XML data formatter
 */
class XMLFormatter implements FormatterInterface
{

	/**
	 * Takes the given data and formats it.
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function format(array $data)
	{
		// SimpleXML is installed but default
		// but best to check, and then provide a fallback.
		if (! extension_loaded('simplexml'))
		{
			// never thrown in travis-ci
			// @codeCoverageIgnoreStart
			throw FormatException::forMissingExtension();
			// @codeCoverageIgnoreEnd
		}

		$output = new \SimpleXMLElement('<?xml version="1.0"?><response></response>');

		$this->arrayToXML($data, $output);

		return $output->asXML();
	}

	//--------------------------------------------------------------------

	/**
	 * A recursive method to convert an array into a valid XML string.
	 *
	 * Written by CodexWorld. Received permission by email on Nov 24, 2016 to use this code.
	 *
	 * @see http://www.codexworld.com/convert-array-to-xml-in-php/
	 *
	 * @param array             $data
	 * @param \SimpleXMLElement $output
	 */
	protected function arrayToXML(array $data, &$output)
	{
		foreach ($data as $key => $value)
		{
			if (is_array($value))
			{
				if (! is_numeric($key))
				{
					$subnode = $output->addChild("$key");
					$this->arrayToXML($value, $subnode);
				}
				else
				{
					$subnode = $output->addChild("item{$key}");
					$this->arrayToXML($value, $subnode);
				}
			}
			else
			{
				$output->addChild("$key", htmlspecialchars("$value"));
			}
		}
	}

	//--------------------------------------------------------------------
}
