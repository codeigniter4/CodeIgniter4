<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use CodeIgniter\HTTP\Exceptions\HTTPException;
use Config\Feature;

/**
 * Class Negotiate
 *
 * Provides methods to negotiate with the HTTP headers to determine the best
 * type match between what the application supports and what the requesting
 * server wants.
 *
 * @see http://tools.ietf.org/html/rfc7231#section-5.3
 */
class Negotiate
{
    /**
     * Request
     *
     * @var IncomingRequest
     */
    protected $request;

    /**
     * Constructor
     */
    public function __construct(?RequestInterface $request = null)
    {
        if ($request instanceof RequestInterface) {
            assert($request instanceof IncomingRequest);

            $this->request = $request;
        }
    }

    /**
     * Stores the request instance to grab the headers from.
     *
     * @return $this
     */
    public function setRequest(RequestInterface $request)
    {
        assert($request instanceof IncomingRequest);

        $this->request = $request;

        return $this;
    }

    /**
     * Determines the best content-type to use based on the $supported
     * types the application says it supports, and the types requested
     * by the client.
     *
     * If no match is found, the first, highest-ranking client requested
     * type is returned.
     *
     * @param bool         $strictMatch If TRUE, will return an empty string when no match found.
     *                                  If FALSE, will return the first supported element.
     * @param list<string> $supported
     */
    public function media(array $supported, bool $strictMatch = false): string
    {
        return $this->getBestMatch($supported, $this->request->getHeaderLine('accept'), true, $strictMatch);
    }

    /**
     * Determines the best charset to use based on the $supported
     * types the application says it supports, and the types requested
     * by the client.
     *
     * If no match is found, the first, highest-ranking client requested
     * type is returned.
     *
     * @param list<string> $supported
     */
    public function charset(array $supported): string
    {
        $match = $this->getBestMatch(
            $supported,
            $this->request->getHeaderLine('accept-charset'),
            false,
            true,
        );

        // If no charset is shown as a match, ignore the directive
        // as allowed by the RFC, and tell it a default value.
        if ($match === '') {
            return 'utf-8';
        }

        return $match;
    }

    /**
     * Determines the best encoding type to use based on the $supported
     * types the application says it supports, and the types requested
     * by the client.
     *
     * If no match is found, the first, highest-ranking client requested
     * type is returned.
     *
     * @param list<string> $supported
     */
    public function encoding(array $supported = []): string
    {
        $supported[] = 'identity';

        return $this->getBestMatch($supported, $this->request->getHeaderLine('accept-encoding'));
    }

    /**
     * Determines the best language to use based on the $supported
     * types the application says it supports, and the types requested
     * by the client.
     *
     * If strict locale negotiation is disabled and no match is found, the first, highest-ranking client requested
     * type is returned.
     *
     * @param list<string> $supported
     */
    public function language(array $supported): string
    {
        if (config(Feature::class)->strictLocaleNegotiation) {
            return $this->getBestLocaleMatch($supported, $this->request->getHeaderLine('accept-language'));
        }

        return $this->getBestMatch($supported, $this->request->getHeaderLine('accept-language'), false, false, true);
    }

    // --------------------------------------------------------------------
    // Utility Methods
    // --------------------------------------------------------------------

    /**
     * Does the grunt work of comparing any of the app-supported values
     * against a given Accept* header string.
     *
     * Portions of this code base on Aura.Accept library.
     *
     * @param list<string> $supported    App-supported values
     * @param string       $header       header string
     * @param bool         $enforceTypes If TRUE, will compare media types and sub-types.
     * @param bool         $strictMatch  If TRUE, will return empty string on no match.
     *                                   If FALSE, will return the first supported element.
     * @param bool         $matchLocales If TRUE, will match locale sub-types to a broad type (fr-FR = fr)
     *
     * @return string Best match
     */
    protected function getBestMatch(
        array $supported,
        ?string $header = null,
        bool $enforceTypes = false,
        bool $strictMatch = false,
        bool $matchLocales = false,
    ): string {
        if ($supported === []) {
            throw HTTPException::forEmptySupportedNegotiations();
        }

        if ($header === null || $header === '') {
            return $strictMatch ? '' : $supported[0];
        }

        $acceptable = $this->parseHeader($header);

        foreach ($acceptable as $accept) {
            // if acceptable quality is zero, skip it.
            if ($accept['q'] === 0.0) {
                continue;
            }

            // if acceptable value is "anything", return the first available
            // that the header does not exclude
            if ($accept['value'] === '*' || $accept['value'] === '*/*') {
                foreach ($supported as $available) {
                    if (! $this->isExcluded($acceptable, $available, $enforceTypes, $matchLocales)) {
                        return $available;
                    }
                }

                continue;
            }

            // If an acceptable value is supported, return it, unless the header
            // excludes it with a more specific value, such as "text/html;q=0"
            // for "text/*"
            foreach ($supported as $available) {
                if ($this->match($accept, $available, $enforceTypes, $matchLocales)
                    && ! $this->isExcluded($acceptable, $available, $enforceTypes, $matchLocales)
                ) {
                    return $available;
                }
            }
        }

        // No matches? Return the first supported element.
        return $strictMatch ? '' : $supported[0];
    }

    /**
     * Try to find the best matching locale. It supports strict locale comparison.
     *
     * If Config\App::$supportedLocales have "en-US" and "en-GB" locales, they can be recognized
     * as two different locales. This method checks first for the strict match, then fallback
     * to the most general locale (in this case "en") ISO 639-1 and finally to the locale variant
     * "en-*" (ISO 639-1 plus "wildcard" for ISO 3166-1 alpha-2).
     *
     * If nothing from above is matched, then it returns the first option from the $supportedLocales array.
     *
     * @param list<string> $supportedLocales App-supported values
     * @param ?string      $header           Compatible 'Accept-Language' header string
     */
    protected function getBestLocaleMatch(array $supportedLocales, ?string $header): string
    {
        if ($supportedLocales === []) {
            throw HTTPException::forEmptySupportedNegotiations();
        }

        if ($header === null || $header === '') {
            return $supportedLocales[0];
        }

        $acceptable      = $this->parseHeader($header);
        $fallbackLocales = [];

        foreach ($acceptable as $accept) {
            // if acceptable quality is zero, skip it.
            if ($accept['q'] === 0.0) {
                continue;
            }

            // if acceptable value is "anything", return the first available
            // that the header does not exclude
            if ($accept['value'] === '*') {
                foreach ($supportedLocales as $locale) {
                    if (! $this->isExcluded($acceptable, $locale, false, true)) {
                        return $locale;
                    }
                }

                continue;
            }

            // look for exact match
            if (in_array($accept['value'], $supportedLocales, true)) {
                return $accept['value'];
            }

            // set a fallback locale
            $fallbackLocales[] = strtok($accept['value'], '-');
        }

        foreach ($fallbackLocales as $fallbackLocale) {
            // look for exact match
            if (in_array($fallbackLocale, $supportedLocales, true)
                && ! $this->isExcluded($acceptable, $fallbackLocale, false, true)
            ) {
                return $fallbackLocale;
            }

            // look for regional locale match
            foreach ($supportedLocales as $locale) {
                if (str_starts_with($locale, $fallbackLocale . '-')
                    && ! $this->isExcluded($acceptable, $locale, false, true)
                ) {
                    return $locale;
                }
            }
        }

        return $supportedLocales[0];
    }

    /**
     * Checks whether the header excludes a supported value, that is, whether
     * the most specific value in the header that matches it has q=0.
     *
     * @param list<array{value: string, q: float, params: array<string, string>}> $acceptable
     * @param bool                                                                $enforceTypes If TRUE, the values are media types.
     * @param bool                                                                $matchLocales If TRUE, the values are language tags.
     */
    private function isExcluded(array $acceptable, string $supported, bool $enforceTypes, bool $matchLocales): bool
    {
        $supported   = $this->parseHeader($supported)[0];
        $specificity = -1;
        $excluded    = false;

        // The header is sorted by quality, so of two equally specific values,
        // the one with the higher quality is used.
        foreach ($acceptable as $accept) {
            $acceptSpecificity = $this->getMatchSpecificity($accept, $supported, $enforceTypes, $matchLocales);

            if ($acceptSpecificity > $specificity) {
                $specificity = $acceptSpecificity;
                $excluded    = $accept['q'] === 0.0;
            }
        }

        return $excluded;
    }

    /**
     * Returns how specifically a header value matches a supported value, or -1
     * if it does not match it. A wildcard is the least specific.
     *
     * A media range matches the media types of its type and subtype that have
     * its parameters. "text/html" is more specific than "text/*", which is more
     * specific than a full wildcard, and a range is more specific than one of
     * the same kind with fewer parameters, so "text/html;level=1" is more
     * specific than "text/html", and "text/*;charset=utf-8" than "text/*"
     * (RFC 9110, Section 12.5.1). Types, subtypes, parameter names and charset
     * values are compared ignoring case (RFC 9110, Section 8.3).
     * A language range matches the tags that equal it or start with it followed
     * by "-", ignoring case, and is more specific with more subtags
     * (RFC 4647, Section 3.3.1). Other values must be equal, ignoring case.
     *
     * @param array{value: string, q: float, params: array<string, string>} $accept
     * @param array{value: string, q: float, params: array<string, string>} $supported
     */
    private function getMatchSpecificity(array $accept, array $supported, bool $enforceTypes, bool $matchLocales): int
    {
        if ($enforceTypes) {
            $range              = strtolower($accept['value']);
            [$aType, $aSubType] = explode('/', $range, 2) + ['', ''];
            [$sType, $sSubType] = explode('/', strtolower($supported['value']), 2) + ['', ''];

            if ($range === '*' || $range === '*/*') {
                $level = 0;
            } elseif ($aType === $sType && $aSubType === '*') {
                $level = 1;
            } elseif ($aType === $sType && $aSubType === $sSubType) {
                $level = 2;
            } else {
                return -1;
            }

            $aParams = array_change_key_case($accept['params']);
            $sParams = array_change_key_case($supported['params']);

            if (isset($aParams['charset'], $sParams['charset'])) {
                $aParams['charset'] = strtolower($aParams['charset']);
                $sParams['charset'] = strtolower($sParams['charset']);
            }

            foreach ($aParams as $label => $value) {
                if (($sParams[$label] ?? null) !== $value) {
                    return -1;
                }
            }

            // A range has at most as many parameters as the value it matches,
            // so it is more specific than any range of a lower level.
            return $level * (count($sParams) + 1) + count($aParams);
        }

        if ($accept['value'] === '*' || $accept['value'] === '*/*') {
            return 0;
        }

        if ($matchLocales) {
            $aTag = strtolower($accept['value']);
            $sTag = strtolower($supported['value']);

            if ($aTag !== $sTag && ! str_starts_with($sTag, $aTag . '-')) {
                return -1;
            }

            return substr_count($aTag, '-') + 1;
        }

        return strcasecmp($accept['value'], $supported['value']) === 0 ? 1 : -1;
    }

    /**
     * Parses an Accept* header into it's multiple values.
     *
     * This is based on code from Aura.Accept library.
     *
     * @return list<array{value: string, q: float, params: array<string, string>}>
     */
    public function parseHeader(string $header): array
    {
        $results    = [];
        $acceptable = explode(',', $header);

        foreach ($acceptable as $value) {
            $pairs = explode(';', $value);

            $value = $pairs[0];

            unset($pairs[0]);

            $parameters = [];

            foreach ($pairs as $pair) {
                if (preg_match(
                    '/^(?P<name>.+?)=(?P<quoted>"|\')?(?P<value>.*?)(?:\k<quoted>)?$/',
                    $pair,
                    $param,
                )) {
                    $parameters[trim($param['name'])] = trim($param['value']);
                }
            }

            $quality = 1.0;

            if (array_key_exists('q', $parameters)) {
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
        usort($results, static function ($a, $b): int {
            if ($a['q'] === $b['q']) {
                $aAst = substr_count($a['value'], '*');
                $bAst = substr_count($b['value'], '*');

                // '*/*' has lower precedence than 'text/*',
                // and 'text/*' has lower priority than 'text/plain'
                //
                // This seems backwards, but needs to be that way
                // due to the way PHP7 handles ordering or array
                // elements created by reference.
                if ($aAst > $bAst) {
                    return 1;
                }

                // If the counts are the same, but one element
                // has more params than another, it has higher precedence.
                //
                // This seems backwards, but needs to be that way
                // due to the way PHP7 handles ordering or array
                // elements created by reference.
                if ($aAst === $bAst) {
                    return count($b['params']) - count($a['params']);
                }

                return 0;
            }

            // Still here? Higher q values have precedence.
            return ($a['q'] > $b['q']) ? -1 : 1;
        });

        return $results;
    }

    /**
     * Match-maker
     *
     * @param bool                                                          $matchLocales
     * @param array{value: string, q: float, params: array<string, string>} $acceptable
     */
    protected function match(array $acceptable, string $supported, bool $enforceTypes = false, $matchLocales = false): bool
    {
        $supported = $this->parseHeader($supported);
        if (count($supported) === 1) {
            $supported = $supported[0];
        }

        // Is it an exact match?
        if ($acceptable['value'] === $supported['value']) {
            return $this->matchParameters($acceptable, $supported);
        }

        // Do we need to compare types/sub-types? Only used
        // by negotiateMedia().
        if ($enforceTypes) {
            return $this->matchTypes($acceptable, $supported);
        }

        // Do we need to match locales against broader locales?
        if ($matchLocales) {
            return $this->matchLocales($acceptable, $supported);
        }

        return false;
    }

    /**
     * Checks two Accept values with matching 'values' to see if their
     * 'params' are the same.
     *
     * @param array{value: string, q: float, params: array<string, string>} $acceptable
     * @param array{value: string, q: float, params: array<string, string>} $supported
     */
    protected function matchParameters(array $acceptable, array $supported): bool
    {
        if (count($acceptable['params']) !== count($supported['params'])) {
            return false;
        }

        foreach ($supported['params'] as $label => $value) {
            if (! isset($acceptable['params'][$label])
                || $acceptable['params'][$label] !== $value
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Compares the types/subtypes of an acceptable Media type and
     * the supported string.
     *
     * @param array{value: string, q: float, params: array<string, string>} $acceptable
     * @param array{value: string, q: float, params: array<string, string>} $supported
     */
    public function matchTypes(array $acceptable, array $supported): bool
    {
        // A value without a subtype, such as a malformed "json", is not a media type.
        if (! str_contains($acceptable['value'], '/') || ! str_contains($supported['value'], '/')) {
            return false;
        }

        // PHPDocumentor v2 cannot parse yet the shorter list syntax,
        // causing no API generation for the file.
        [$aType, $aSubType] = explode('/', $acceptable['value']);
        [$sType, $sSubType] = explode('/', $supported['value']);

        // If the types don't match, we're done.
        if ($aType !== $sType) {
            return false;
        }

        // If there's an asterisk, we're cool
        if ($aSubType === '*') {
            return true;
        }

        // Otherwise, subtypes must match also.
        return $aSubType === $sSubType;
    }

    /**
     * Will match locales against their broader pairs, so that fr-FR would
     * match a supported localed of fr
     *
     * @param array{value: string, q: float, params: array<string, string>} $acceptable
     * @param array{value: string, q: float, params: array<string, string>} $supported
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
