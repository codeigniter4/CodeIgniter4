<?php

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

use DOMNamedNodeMap;
use DOMNode;
use DOMNodeList;
use Kint\Object\BasicObject;
use Kint\Object\InstanceObject;
use Kint\Object\Representation\Representation;

/**
 * The DOMDocument parser plugin is particularly useful as it is both the only
 * way to see inside the DOMNode without print_r, and the only way to see mixed
 * text and node inside XML (SimpleXMLElement will strip out the text).
 */
class DOMDocumentPlugin extends Plugin
{
    /**
     * List of properties to skip parsing.
     *
     * The properties of a DOMNode can do a *lot* of damage to debuggers. The
     * DOMNode contains not one, not two, not three, not four, not 5, not 6,
     * not 7 but 8 different ways to recurse into itself:
     * * firstChild
     * * lastChild
     * * previousSibling
     * * nextSibling
     * * ownerDocument
     * * parentNode
     * * childNodes
     * * attributes
     *
     * All of this combined: the tiny SVGs used as the caret in Kint are already
     * enough to make parsing and rendering take over a second, and send memory
     * usage over 128 megs. So we blacklist every field we don't strictly need
     * and hope that that's good enough.
     *
     * In retrospect - this is probably why print_r does the same
     *
     * @var array
     */
    public static $blacklist = array(
        'parentNode' => 'DOMNode',
        'firstChild' => 'DOMNode',
        'lastChild' => 'DOMNode',
        'previousSibling' => 'DOMNode',
        'nextSibling' => 'DOMNode',
        'ownerDocument' => 'DOMDocument',
    );

    /**
     * Show all properties and methods.
     *
     * @var bool
     */
    public static $verbose = false;

    public function getTypes()
    {
        return array('object');
    }

    public function getTriggers()
    {
        return Parser::TRIGGER_SUCCESS;
    }

    public function parse(&$var, BasicObject &$o, $trigger)
    {
        if (!$o instanceof InstanceObject) {
            return;
        }

        if ($var instanceof DOMNamedNodeMap || $var instanceof DOMNodeList) {
            return $this->parseList($var, $o, $trigger);
        }

        if ($var instanceof DOMNode) {
            return $this->parseNode($var, $o);
        }
    }

    protected function parseList(&$var, InstanceObject &$o, $trigger)
    {
        // Recursion should never happen, should always be stopped at the parent
        // DOMNode.  Depth limit on the other hand we're going to skip since
        // that would show an empty iterator and rather useless. Let the depth
        // limit hit the children (DOMNodeList only has DOMNode as children)
        if ($trigger & Parser::TRIGGER_RECURSION) {
            return;
        }

        $o->size = $var->length;
        if (0 === $o->size) {
            $o->replaceRepresentation(new Representation('Iterator'));
            $o->size = null;

            return;
        }

        // Depth limit
        // Make empty iterator representation since we need it in DOMNode to point out depth limits
        if ($this->parser->getDepthLimit() && $o->depth + 1 >= $this->parser->getDepthLimit()) {
            $b = new BasicObject();
            $b->name = $o->classname.' Iterator Contents';
            $b->access_path = 'iterator_to_array('.$o->access_path.')';
            $b->depth = $o->depth + 1;
            $b->hints[] = 'depth_limit';

            $r = new Representation('Iterator');
            $r->contents = array($b);
            $o->replaceRepresentation($r, 0);

            return;
        }

        $data = \iterator_to_array($var);

        $r = new Representation('Iterator');
        $o->replaceRepresentation($r, 0);

        foreach ($data as $key => $item) {
            $base_obj = new BasicObject();
            $base_obj->depth = $o->depth + 1;
            $base_obj->name = $item->nodeName;

            if ($o->access_path) {
                if ($var instanceof DOMNamedNodeMap) {
                    $base_obj->access_path = $o->access_path.'->getNamedItem('.\var_export($key, true).')';
                } elseif ($var instanceof DOMNodeList) {
                    $base_obj->access_path = $o->access_path.'->item('.\var_export($key, true).')';
                } else {
                    $base_obj->access_path = 'iterator_to_array('.$o->access_path.')';
                }
            }

            $r->contents[] = $this->parser->parse($item, $base_obj);
        }
    }

    protected function parseNode(&$var, InstanceObject &$o)
    {
        // Fill the properties
        // They can't be enumerated through reflection or casting,
        // so we have to trust the docs and try them one at a time
        $known_properties = array(
            'nodeValue',
            'childNodes',
            'attributes',
        );

        if (self::$verbose) {
            $known_properties = array(
                'nodeName',
                'nodeValue',
                'nodeType',
                'parentNode',
                'childNodes',
                'firstChild',
                'lastChild',
                'previousSibling',
                'nextSibling',
                'attributes',
                'ownerDocument',
                'namespaceURI',
                'prefix',
                'localName',
                'baseURI',
                'textContent',
            );
        }

        $childNodes = array();
        $attributes = array();

        $rep = $o->value;

        foreach ($known_properties as $prop) {
            $prop_obj = $this->parseProperty($o, $prop, $var);
            $rep->contents[] = $prop_obj;

            if ('childNodes' === $prop) {
                $childNodes = $prop_obj->getRepresentation('iterator');
            } elseif ('attributes' === $prop) {
                $attributes = $prop_obj->getRepresentation('iterator');
            }
        }

        if (!self::$verbose) {
            $o->removeRepresentation('methods');
            $o->removeRepresentation('properties');
        }

        // Attributes and comments and text nodes don't
        // need children or attributes of their own
        if (\in_array($o->classname, array('DOMAttr', 'DOMText', 'DOMComment'), true)) {
            return;
        }

        // Set the attributes
        if ($attributes) {
            $a = new Representation('Attributes');
            foreach ($attributes->contents as $attribute) {
                $a->contents[] = self::textualNodeToString($attribute);
            }
            $o->addRepresentation($a, 0);
        }

        // Set the children
        if ($childNodes) {
            $c = new Representation('Children');

            if (1 === \count($childNodes->contents) && ($node = \reset($childNodes->contents)) && \in_array('depth_limit', $node->hints, true)) {
                $n = new InstanceObject();
                $n->transplant($node);
                $n->name = 'childNodes';
                $n->classname = 'DOMNodeList';
                $c->contents = array($n);
            } else {
                foreach ($childNodes->contents as $index => $node) {
                    // Shortcircuit text nodes to plain strings
                    if ('DOMText' === $node->classname || 'DOMComment' === $node->classname) {
                        $node = self::textualNodeToString($node);

                        // And remove them if they're empty
                        if (\ctype_space($node->value->contents) || '' === $node->value->contents) {
                            continue;
                        }
                    }

                    $c->contents[] = $node;
                }
            }

            $o->addRepresentation($c, 0);
        }

        if (isset($c) && \count($c->contents)) {
            $o->size = \count($c->contents);
        }

        if (!$o->size) {
            $o->size = null;
        }
    }

    protected function parseProperty(InstanceObject $o, $prop, &$var)
    {
        // Duplicating (And slightly optimizing) the Parser::parseObject() code here
        $base_obj = new BasicObject();
        $base_obj->depth = $o->depth + 1;
        $base_obj->owner_class = $o->classname;
        $base_obj->name = $prop;
        $base_obj->operator = BasicObject::OPERATOR_OBJECT;
        $base_obj->access = BasicObject::ACCESS_PUBLIC;

        if (null !== $o->access_path) {
            $base_obj->access_path = $o->access_path;

            if (\preg_match('/^[A-Za-z0-9_]+$/', $base_obj->name)) {
                $base_obj->access_path .= '->'.$base_obj->name;
            } else {
                $base_obj->access_path .= '->{'.\var_export($base_obj->name, true).'}';
            }
        }

        if (!isset($var->{$prop})) {
            $base_obj->type = 'null';
        } elseif (isset(self::$blacklist[$prop])) {
            $b = new InstanceObject();
            $b->transplant($base_obj);
            $base_obj = $b;

            $base_obj->hints[] = 'blacklist';
            $base_obj->classname = self::$blacklist[$prop];
        } elseif ('attributes' === $prop) {
            $base_obj = $this->parser->parseDeep($var->{$prop}, $base_obj);
        } else {
            $base_obj = $this->parser->parse($var->{$prop}, $base_obj);
        }

        return $base_obj;
    }

    protected static function textualNodeToString(InstanceObject $o)
    {
        if (empty($o->value) || empty($o->value->contents) || empty($o->classname)) {
            return;
        }

        if (!\in_array($o->classname, array('DOMText', 'DOMAttr', 'DOMComment'), true)) {
            return;
        }

        foreach ($o->value->contents as $property) {
            if ('nodeValue' === $property->name) {
                $ret = clone $property;
                $ret->name = $o->name;

                return $ret;
            }
        }
    }
}
