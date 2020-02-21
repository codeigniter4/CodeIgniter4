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

namespace Kint\Renderer;

use Kint\Object\BasicObject;
use Kint\Object\InstanceObject;

abstract class Renderer
{
    const SORT_NONE = 0;
    const SORT_VISIBILITY = 1;
    const SORT_FULL = 2;

    protected $call_info = array();
    protected $statics = array();
    protected $show_trace = true;

    abstract public function render(BasicObject $o);

    abstract public function renderNothing();

    public function setCallInfo(array $info)
    {
        if (!isset($info['params'])) {
            $info['params'] = null;
        }

        if (!isset($info['modifiers']) || !\is_array($info['modifiers'])) {
            $info['modifiers'] = array();
        }

        if (!isset($info['callee'])) {
            $info['callee'] = null;
        }

        if (!isset($info['caller'])) {
            $info['caller'] = null;
        }

        if (!isset($info['trace']) || !\is_array($info['trace'])) {
            $info['trace'] = array();
        }

        $this->call_info = array(
            'params' => $info['params'],
            'modifiers' => $info['modifiers'],
            'callee' => $info['callee'],
            'caller' => $info['caller'],
            'trace' => $info['trace'],
        );
    }

    public function getCallInfo()
    {
        return $this->call_info;
    }

    public function setStatics(array $statics)
    {
        $this->statics = $statics;
        $this->setShowTrace(!empty($statics['display_called_from']));
    }

    public function getStatics()
    {
        return $this->statics;
    }

    public function setShowTrace($show_trace)
    {
        $this->show_trace = $show_trace;
    }

    public function getShowTrace()
    {
        return $this->show_trace;
    }

    /**
     * Returns the first compatible plugin available.
     *
     * @param array $plugins Array of hints to class strings
     * @param array $hints   Array of object hints
     *
     * @return array Array of hints to class strings filtered and sorted by object hints
     */
    public function matchPlugins(array $plugins, array $hints)
    {
        $out = array();

        foreach ($hints as $key) {
            if (isset($plugins[$key])) {
                $out[$key] = $plugins[$key];
            }
        }

        return $out;
    }

    public function filterParserPlugins(array $plugins)
    {
        return $plugins;
    }

    public function preRender()
    {
        return '';
    }

    public function postRender()
    {
        return '';
    }

    public static function sortPropertiesFull(BasicObject $a, BasicObject $b)
    {
        $sort = BasicObject::sortByAccess($a, $b);
        if ($sort) {
            return $sort;
        }

        $sort = BasicObject::sortByName($a, $b);
        if ($sort) {
            return $sort;
        }

        return InstanceObject::sortByHierarchy($a->owner_class, $b->owner_class);
    }

    /**
     * Sorts an array of BasicObject.
     *
     * @param BasicObject[] $contents Object properties to sort
     * @param int           $sort
     *
     * @return BasicObject[]
     */
    public static function sortProperties(array $contents, $sort)
    {
        switch ($sort) {
            case self::SORT_VISIBILITY:
                /** @var array<array-key, BasicObject[]> Containers to quickly stable sort by type */
                $containers = array(
                    BasicObject::ACCESS_PUBLIC => array(),
                    BasicObject::ACCESS_PROTECTED => array(),
                    BasicObject::ACCESS_PRIVATE => array(),
                    BasicObject::ACCESS_NONE => array(),
                );

                foreach ($contents as $item) {
                    $containers[$item->access][] = $item;
                }

                return \call_user_func_array('array_merge', $containers);
            case self::SORT_FULL:
                \usort($contents, array('Kint\\Renderer\\Renderer', 'sortPropertiesFull'));
                // no break
            default:
                return $contents;
        }
    }
}
