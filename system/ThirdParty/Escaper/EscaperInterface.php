<?php

declare(strict_types=1);

namespace Laminas\Escaper;

/**
 * Interface for context specific methods for use in secure output escaping
 */
interface EscaperInterface
{
    /**
     * Escape a string for the HTML Body context where there are very few characters
     * of special meaning. Internally this will use htmlspecialchars().
     *
     * @return ($string is non-empty-string ? non-empty-string : string)
     */
    public function escapeHtml(string $string);

    /**
     * Escape a string for the HTML Attribute context. We use an extended set of characters
     * to escape that are not covered by htmlspecialchars() to cover cases where an attribute
     * might be unquoted or quoted illegally (e.g. backticks are valid quotes for IE).
     *
     * @return ($string is non-empty-string ? non-empty-string : string)
     */
    public function escapeHtmlAttr(string $string);

    /**
     * Escape a string for the Javascript context. This does not use json_encode(). An extended
     * set of characters are escaped beyond ECMAScript's rules for Javascript literal string
     * escaping in order to prevent misinterpretation of Javascript as HTML leading to the
     * injection of special characters and entities. The escaping used should be tolerant
     * of cases where HTML escaping was not applied on top of Javascript escaping correctly.
     * Backslash escaping is not used as it still leaves the escaped character as-is and so
     * is not useful in a HTML context.
     *
     * @return ($string is non-empty-string ? non-empty-string : string)
     */
    public function escapeJs(string $string);

    /**
     * Escape a string for the URI or Parameter contexts. This should not be used to escape
     * an entire URI - only a subcomponent being inserted. The function is a simple proxy
     * to rawurlencode() which now implements RFC 3986 since PHP 5.3 completely.
     *
     * @return ($string is non-empty-string ? non-empty-string : string)
     */
    public function escapeUrl(string $string);

    /**
     * Escape a string for the CSS context. CSS escaping can be applied to any string being
     * inserted into CSS and escapes everything except alphanumerics.
     *
     * @return ($string is non-empty-string ? non-empty-string : string)
     */
    public function escapeCss(string $string);
}
