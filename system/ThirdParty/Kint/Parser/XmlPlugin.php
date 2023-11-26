<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2013 Jonathan Vollebregt (jnvsor@gmail.com), Rokas Šleinius (raveren@gmail.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Kint\Parser;

use DOMDocument;
use Exception;
use Kint\Zval\Representation\Representation;
use Kint\Zval\Value;

class XmlPlugin extends AbstractPlugin
{
    /**
     * Which method to parse the variable with.
     *
     * DOMDocument provides more information including the text between nodes,
     * however it's memory usage is very high and it takes longer to parse and
     * render. Plus it's a pain to work with. So SimpleXML is the default.
     *
     * @var string
     */
    public static $parse_method = 'SimpleXML';

    public function getTypes(): array
    {
        return ['string'];
    }

    public function getTriggers(): int
    {
        return Parser::TRIGGER_SUCCESS;
    }

    public function parse(&$var, Value &$o, int $trigger): void
    {
        if ('<?xml' !== \substr($var, 0, 5)) {
            return;
        }

        if (!\method_exists(\get_class($this), 'xmlTo'.self::$parse_method)) {
            return;
        }

        $xml = \call_user_func([\get_class($this), 'xmlTo'.self::$parse_method], $var, $o->access_path);

        if (empty($xml)) {
            return;
        }

        [$xml, $access_path, $name] = $xml;

        $base_obj = new Value();
        $base_obj->depth = $o->depth + 1;
        $base_obj->name = $name;
        $base_obj->access_path = $access_path;

        $r = new Representation('XML');
        $r->contents = $this->parser->parse($xml, $base_obj);

        $o->addRepresentation($r, 0);
    }

    protected static function xmlToSimpleXML(string $var, ?string $parent_path): ?array
    {
        $errors = \libxml_use_internal_errors(true);
        try {
            $xml = \simplexml_load_string($var);
        } catch (Exception $e) {
            return null;
        } finally {
            \libxml_use_internal_errors($errors);
        }

        if (false === $xml) {
            return null;
        }

        if (null === $parent_path) {
            $access_path = null;
        } else {
            $access_path = 'simplexml_load_string('.$parent_path.')';
        }

        $name = $xml->getName();

        return [$xml, $access_path, $name];
    }

    /**
     * Get the DOMDocument info.
     *
     * If it errors loading then we wouldn't have gotten this far in the first place.
     *
     * @psalm-param non-empty-string $var         The XML string
     *
     * @param ?string $parent_path The path to the parent, in this case the XML string
     *
     * @return ?array The root element DOMNode, the access path, and the root element name
     */
    protected static function xmlToDOMDocument(string $var, ?string $parent_path): ?array
    {
        // There's no way to check validity in DOMDocument without making errors. For shame!
        if (!self::xmlToSimpleXML($var, $parent_path)) {
            return null;
        }

        $xml = new DOMDocument();
        $xml->loadXML($var);

        if ($xml->childNodes->count() > 1) {
            $xml = $xml->childNodes;
            $access_path = 'childNodes';
        } else {
            $xml = $xml->firstChild;
            $access_path = 'firstChild';
        }

        if (null === $parent_path) {
            $access_path = null;
        } else {
            $access_path = '(function($s){$x = new \\DomDocument(); $x->loadXML($s); return $x;})('.$parent_path.')->'.$access_path;
        }

        $name = $xml->nodeName ?? null;

        return [$xml, $access_path, $name];
    }
}
