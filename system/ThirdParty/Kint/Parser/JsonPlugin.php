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

use Kint\Zval\Representation\Representation;
use Kint\Zval\Value;

class JsonPlugin extends Plugin
{
    public function getTypes()
    {
        return ['string'];
    }

    public function getTriggers()
    {
        return Parser::TRIGGER_SUCCESS;
    }

    public function parse(&$var, Value &$o, $trigger)
    {
        if (!isset($var[0]) || ('{' !== $var[0] && '[' !== $var[0])) {
            return;
        }

        $json = \json_decode($var, true);

        if (!$json) {
            return;
        }

        $json = (array) $json;

        $base_obj = new Value();
        $base_obj->depth = $o->depth;

        if ($o->access_path) {
            $base_obj->access_path = 'json_decode('.$o->access_path.', true)';
        }

        $r = new Representation('Json');
        $r->contents = $this->parser->parse($json, $base_obj);

        if (!\in_array('depth_limit', $r->contents->hints, true)) {
            $r->contents = $r->contents->value->contents;
        }

        $o->addRepresentation($r, 0);
    }
}
