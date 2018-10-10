<?php

namespace CodeIgniter\HTTP;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
use CodeIgniter\HTTP\Exceptions\HTTPException;

/**
 * Class Negotiate
 *
 * Provides methods to negotiate with the HTTP headers to determine the best
 * type match between what the application supports and what the requesting
 * getServer wants.
 *
 * @see http://tools.ietf.org/html/rfc7231#section-5.3
 * @package CodeIgniter\HTTP
 */
class Negotiate
{

	/**
	 * Request
	 *
	 * @var \CodeIgniter\HTTP\RequestInterface|\CodeIgniter\HTTP\IncomingRequest
	 */
	protected $request;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param \CodeIgniter\HTTP\RequestInterface $request
	 */
	public function __construct(\CodeIgniter\HTTP\RequestInterface $request = null)
	{
		if ( ! is_null($request))
		{
			$this->request = $request;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Stores the request instance to grab the headers from.
	 *
	 * @param RequestInterface $request
	 *
	 * @return $this
	 */
	public function setRequest(\CodeIgniter\HTTP\RequestInterface $request)
	{
		$this->request = $request;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Determines the best content-type to use based on the $supported
	 * types the application says it supports, and the types requested
	 * by the client.
	 *
	 * If no match is found, the first, highest-ranking client requested
	 * type is returned.
	 *
	 * @param array $supported
	 * @param bool  $strictMatch If TRUE, will return an empty string when no match found.
	 *                           If FALSE, will return the first supported element.
	 *
	 * @return string
	 */
	public function media(array $supported, bool $strictMatch = false): string
	{
		return $this->getBestMatch($supported, $this->request->getHeaderLine('accept'), true, $strictMatch);
	}

	//--------------------------------------------------------------------

	/**
	 * Determines the best charset to use based on the $supported
	 * types the application says it supports, and the types requested
	 * by the client.
	 *
	 * If no match is found, the first, highest-ranking client requested
	 * type is returned.
	 *
	 * @param array $supported
	 *
	 * @return string
	 */
	public function charset(array $supported): string
	{
		$match = $this->getBestMatch($supported, $this->request->getHeaderLine('accept-charset'), false, true);

		// If no charset is shown as a match, ignore the directive
		// as allowed by the RFC, and tell it a default value.
		if (empty($match))
		{
			return 'utf-8';
		}

		return $match;
	}

	//--------------------------------------------------------------------

	/**
	 * Determines the best encoding type to use based on the $supported
	 * types the application says it supports, and the types requested
	 * by the client.
	 *
	 * If no match is found, the first, highest-ranking client requested
	 * type is returned.
	 *
	 * @param array $supported
	 *
	 * @return string
	 */
	public function encoding(array $supported = []): string
	{
		array_push($supported, 'identity');

		return $this->getBestMatch($supported, $this->request->getHeaderLine('accept-encoding'));
	}

	//--------------------------------------------------------------------

	/**
	 * Determines the best language to use based on the $supported
	 * types the application says it supports, and the types requested
	 * by the client.
	 *
	 * If no match is found, the first, highest-ranking client requested
	 * type is returned.
	 *
	 * @param array $supported
	 *
	 * @return string
	 */
	public function language(array $supported): string
	{
		return $this->getBestMatch($supported, $this->request->getHeaderLine('accept-language'));
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Utility Methods
	//--------------------------------------------------------------------

	/**
	 * Does the grunt work of comparing any of the app-supported values
	 * against a given Accept* header string.
	 *
	 * Portions of this code base on Aura.Accept library.
	 *
	 * @param array  $supported    App-supported values
	 * @param string $header       header string
	 * @param bool   $enforceTypes If TRUE, will compare media types and sub-types.
	 * @param bool   $strictMatch  If TRUE, will return empty string on no match.
	 *                             If FALSE, will return the first supported element.
	 *
	 * @return string Best match
	 */
	protected function getBestMatch(array $supported, string $header = null, bool $enforceTypes = false, bool $strictMatch = false): string
	{
		if (empty($supported))
		{
			throw HTTPException::forEmptySupportedNegotiations();
		}

		if (empty($header))
		{
			return $strictMatch ? '' : $supported[0];
		}

		$acceptable = $this->parseHeader($header);

		foreach ($acceptable as $accept)
		{
			// if acceptable quality is zero, skip it.
			if ($accept['q'] == 0)
			{
				continue;
			}

			// if acceptable value is "anything", return the first available
			if ($accept['value'] == '*' || $accept['value'] == '*/*')
			{
				return $supported[0];
			}

			// If an acceptable value is supported, return it
			foreach ($supported as $available)
			{
				if ($this->match($accept, $available, $enforceTypes))
				{
					return $available;
				}
			}
		}

		// No matches? Return the first supported element.
		return $strictMatch ? '' : $supported[0];
	}

	//--------------------------------------------------------------------

	/**
	 * Parses an Accept* header into it's multiple values.
	 *
	 * This is based on code from Aura.Accept library.
	 *
	 * @param string $header
	 *
	 * @return array
	 */
	public function parseHeader(string $header)
	{
		$results = [];
		$acceptable = explode(',', $header);

		foreach ($acceptable as $value)
		{
			$pairs = explode(';', $value);

			$value = $pairs[0];

			unset($pairs[0]);

			$parameters = [];

			foreach ($pairs as $pair)
			{
				$param = [];
				preg_match(
						'/^(?P<name>.+?)=(?P<quoted>"|\')?(?P<value>.*?)(?:\k<quoted>)?$/', $pair, $param
				);
				$parameters[trim($param['name'])] = trim($param['value']);
			}

			$quality = 1.0;

			if (array_key_exists('q', $parameters))
			{
				$quality = $parameters['q'];
				unset($parameters['q']);
			}

			$results[] = [
				'value' => trim($value),
				'q' => (float) $quality,
				'params' => $parameters
			];
		}

		// Sort to get the highest results first
		usort($results, function ($a, $b) {
			if ($a['q'] == $b['q'])
			{
				$a_ast = substr_count($a['value'], '*');
				$b_ast = substr_count($b['value'], '*');

				// '*/*' has lower precedence than 'text/*',
				// and 'text/*' has lower priority than 'text/plain'
				//
				// This seems backwards, but needs to be that way
				// due to the way PHP7 handles ordering or array
				// elements created by reference.
				if ($a_ast > $b_ast)
				{
					return 1;
				}

				// If the counts are the same, but one element
				// has more params than another, it has higher precedence.
				//
				// This seems backwards, but needs to be that way
				// due to the way PHP7 handles ordering or array
				// elements created by reference.
				if ($a_ast == $b_ast)
				{
					return count($b['params']) - count($a['params']);
				}

				return 0;
			}

			// Still here? Higher q values have precedence.
			return ($a['q'] > $b['q']) ? -1 : 1;
		});

		return $results;
	}

	//--------------------------------------------------------------------

	/**
	 * Match-maker
	 *
	 * @param array $acceptable
	 * @param string $supported
	 * @param bool $enforceTypes
	 * @return boolean
	 */
	protected function match(array $acceptable, string $supported, bool $enforceTypes = false)
	{
		$supported = $this->parseHeader($supported);
		if (is_array($supported) && count($supported) == 1)
		{
			$supported = $supported[0];
		}

		// Is it an exact match?
		if ($acceptable['value'] == $supported['value'])
		{
			return $this->matchParameters($acceptable, $supported);
		}

		// Do we need to compare types/sub-types? Only used
		// by negotiateMedia().
		if ($enforceTypes)
		{
			return $this->matchTypes($acceptable, $supported);
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Checks two Accept values with matching 'values' to see if their
	 * 'params' are the same.
	 *
	 * @param array $acceptable
	 * @param array $supported
	 *
	 * @return bool
	 */
	protected function matchParameters(array $acceptable, array $supported): bool
	{
		if (count($acceptable['params']) != count($supported['params']))
		{
			return false;
		}

		foreach ($supported['params'] as $label => $value)
		{
			if ( ! isset($acceptable['params'][$label]) ||
					$acceptable['params'][$label] != $value)
			{
				return false;
			}
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Compares the types/subtypes of an acceptable Media type and
	 * the supported string.
	 *
	 * @param array $acceptable
	 * @param array $supported
	 *
	 * @return bool
	 */
	public function matchTypes(array $acceptable, array $supported): bool
	{
		list($aType, $aSubType) = explode('/', $acceptable['value']);
		list($sType, $sSubType) = explode('/', $supported['value']);

		// If the types don't match, we're done.
		if ($aType != $sType)
		{
			return false;
		}

		// If there's an asterisk, we're cool
		if ($aSubType == '*')
		{
			return true;
		}

		// Otherwise, subtypes must match also.
		return $aSubType == $sSubType;
	}

	//--------------------------------------------------------------------
}
