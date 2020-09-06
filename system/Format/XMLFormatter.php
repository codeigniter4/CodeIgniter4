<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
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
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Format;

use CodeIgniter\Format\Exceptions\FormatException;
use Config\Format;

/**
 * XML data formatter
 */
class XMLFormatter implements FormatterInterface
{
	/**
	 * Takes the given data and formats it.
	 *
	 * @param mixed $data
	 *
	 * @return string|boolean (XML string | false)
	 */
	public function format($data)
	{
		$config = new Format();

		// SimpleXML is installed but default
		// but best to check, and then provide a fallback.
		if (! extension_loaded('simplexml'))
		{
			// never thrown in travis-ci
			// @codeCoverageIgnoreStart
			throw FormatException::forMissingExtension();
			// @codeCoverageIgnoreEnd
		}

		$options = $config->formatterOptions['application/xml'] ?? 0;
		$output  = new \SimpleXMLElement('<?xml version="1.0"?><response></response>', $options);

		$this->arrayToXML((array) $data, $output);

		return $output->asXML();
	}

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
				$key     = $this->normalizeXMLTag($key);
				$subnode = $output->addChild("$key");
				$this->arrayToXML($value, $subnode);
			}
			else
			{
				$key = $this->normalizeXMLTag($key);
				$output->addChild("$key", htmlspecialchars("$value"));
			}
		}
	}

	/**
	 * Normalizes tags into the allowed by W3C.
	 * Regex adopted from this StackOverflow answer.
	 *
	 * @param string|integer $key
	 *
	 * @return string
	 *
	 * @see https://stackoverflow.com/questions/60001029/invalid-characters-in-xml-tag-name
	 */
	protected function normalizeXMLTag($key)
	{
		$startChar = 'A-Z_a-z' .
			'\\x{C0}-\\x{D6}\\x{D8}-\\x{F6}\\x{F8}-\\x{2FF}\\x{370}-\\x{37D}' .
			'\\x{37F}-\\x{1FFF}\\x{200C}-\\x{200D}\\x{2070}-\\x{218F}' .
			'\\x{2C00}-\\x{2FEF}\\x{3001}-\\x{D7FF}\\x{F900}-\\x{FDCF}' .
			'\\x{FDF0}-\\x{FFFD}\\x{10000}-\\x{EFFFF}';
		$validName = $startChar . '\\.\\d\\x{B7}\\x{300}-\\x{36F}\\x{203F}-\\x{2040}';

		$key = trim($key);
		$key = preg_replace("/[^{$validName}-]+/u", '', $key);
		$key = preg_replace("/^[^{$startChar}]+/u", 'item$0', $key);
		$key = preg_replace('/^(xml).*/iu', 'item$0', $key); // XML is a reserved starting word

		return $key;
	}
}
