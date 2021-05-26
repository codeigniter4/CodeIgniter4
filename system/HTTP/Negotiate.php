<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use CodeIgniter\HTTP\Exceptions\HTTPException;

/**
 * Class Negotiate
 *
 * Provides methods to negotiate with the HTTP headers to determine the best
 * type match between what the application supports and what the requesting
 * getServer wants.
 *
 * @see http://tools.ietf.org/html/rfc7231#section-5.3
 */
class Negotiate
{
	/**
	 * Request
	 *
	 * @var RequestInterface|IncomingRequest
	 */
	protected $request;

	//--------------------------------------------------------------------
	/**
	 * Constructor
	 *
	 * @param RequestInterface|null $request
	 */
	public function __construct(RequestInterface $request = null)
	{
		if (! is_null($request))
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
	public function setRequest(RequestInterface $request)
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
	 * @param array   $supported
	 * @param boolean $strictMatch If TRUE, will return an empty string when no match found.
	 *                             If FALSE, will return the first supported element.
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
		$supported[] = 'identity';

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
		return $this->getBestMatch($supported, $this->request->getHeaderLine('accept-language'), false, false, true);
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
	 * @param array   $supported    App-supported values
	 * @param string  $header       header string
	 * @param boolean $enforceTypes If TRUE, will compare media types and sub-types.
	 * @param boolean $strictMatch  If TRUE, will return empty string on no match.
	 *                              If FALSE, will return the first supported element.
	 * @param boolean $matchLocales If TRUE, will match locale sub-types to a broad type (fr-FR = fr)
	 *
	 * @return string Best match
	 */
	protected function getBestMatch(array $supported, string $header = null, bool $enforceTypes = false, bool $strictMatch = false, bool $matchLocales = false): string
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
			if ($accept['q'] === 0.0)
			{
				continue;
			}

			// if acceptable value is "anything", return the first available
			if ($accept['value'] === '*' || $accept['value'] === '*/*')
			{
				return $supported[0];
			}

			// If an acceptable value is supported, return it
			foreach ($supported as $available)
			{
				if ($this->match($accept, $available, $enforceTypes, $matchLocales))
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
	public function parseHeader(string $header): array
	{
		$results    = [];
		$acceptable = explode(',', $header);

		foreach ($acceptable as $value)
		{
			$pairs = explode(';', $value);

			$value = $pairs[0];

			unset($pairs[0]);

			$parameters = [];

			foreach ($pairs as $pair)
			{
				if (preg_match(
					'/^(?P<name>.+?)=(?P<quoted>"|\')?(?P<value>.*?)(?:\k<quoted>)?$/',
					$pair,
					$param
				))
				{
					$parameters[trim($param['name'])] = trim($param['value']);
				}
			}

			$quality = 1.0;

			if (array_key_exists('q', $parameters))
			{
				$quality = $parameters['q'];
				unset($parameters['q']);
			}

			$results[] = [
				'value'  => trim($value),
				'q'      => (float) $quality,
				'params' => $parameters,
			];
		}

		// Sort to get the highest results first
		usort($results, function ($a, $b) {
			if ($a['q'] === $b['q'])
			{
				$aAst = substr_count($a['value'], '*');
				$bAst = substr_count($b['value'], '*');

				// '*/*' has lower precedence than 'text/*',
				// and 'text/*' has lower priority than 'text/plain'
				//
				// This seems backwards, but needs to be that way
				// due to the way PHP7 handles ordering or array
				// elements created by reference.
				if ($aAst > $bAst)
				{
					return 1;
				}

				// If the counts are the same, but one element
				// has more params than another, it has higher precedence.
				//
				// This seems backwards, but needs to be that way
				// due to the way PHP7 handles ordering or array
				// elements created by reference.
				if ($aAst === $bAst)
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
	 * @param array   $acceptable
	 * @param string  $supported
	 * @param boolean $enforceTypes
	 * @param boolean $matchLocales
	 *
	 * @return boolean
	 */
	protected function match(array $acceptable, string $supported, bool $enforceTypes = false, $matchLocales = false): bool
	{
		$supported = $this->parseHeader($supported);
		if (is_array($supported) && count($supported) === 1)
		{
			$supported = $supported[0];
		}

		// Is it an exact match?
		if ($acceptable['value'] === $supported['value'])
		{
			return $this->matchParameters($acceptable, $supported);
		}

		// Do we need to compare types/sub-types? Only used
		// by negotiateMedia().
		if ($enforceTypes)
		{
			return $this->matchTypes($acceptable, $supported);
		}

		// Do we need to match locales against broader locales?
		if ($matchLocales)
		{
			return $this->matchLocales($acceptable, $supported);
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
	 * @return boolean
	 */
	protected function matchParameters(array $acceptable, array $supported): bool
	{
		if (count($acceptable['params']) !== count($supported['params']))
		{
			return false;
		}

		foreach ($supported['params'] as $label => $value)
		{
			if (! isset($acceptable['params'][$label])
				|| $acceptable['params'][$label] !== $value
			)
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
	 * @return boolean
	 */
	public function matchTypes(array $acceptable, array $supported): bool
	{
		// PHPDocumentor v2 cannot parse yet the shorter list syntax,
		// causing no API generation for the file.
		[$aType, $aSubType] = explode('/', $acceptable['value']);
		[$sType, $sSubType] = explode('/', $supported['value']);

		// If the types don't match, we're done.
		if ($aType !== $sType)
		{
			return false;
		}

		// If there's an asterisk, we're cool
		if ($aSubType === '*')
		{
			return true;
		}

		// Otherwise, subtypes must match also.
		return $aSubType === $sSubType;
	}

	//--------------------------------------------------------------------

	/**
	 * Will match locales against their broader pairs, so that fr-FR would
	 * match a supported localed of fr
	 *
	 * @param array $acceptable
	 * @param array $supported
	 *
	 * @return boolean
	 */
	public function matchLocales(array $acceptable, array $supported): bool
	{
		$aBroad = mb_strpos($acceptable['value'], '-') > 0
			? mb_substr($acceptable['value'], 0, mb_strpos($acceptable['value'], '-'))
			: $acceptable['value'];
		$sBroad = mb_strpos($supported['value'], '-') > 0
			? mb_substr($supported['value'], 0, mb_strpos($supported['value'], '-'))
			: $supported['value'];

		return strtolower($aBroad) === strtolower($sBroad);
	}
}
