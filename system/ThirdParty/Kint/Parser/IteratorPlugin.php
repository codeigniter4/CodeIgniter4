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

use Dom\NamedNodeMap;
use Dom\NodeList;
use DOMNamedNodeMap;
use DOMNodeList;
use Kint\Value\AbstractValue;
use Kint\Value\ArrayValue;
use Kint\Value\Context\BaseContext;
use Kint\Value\InstanceValue;
use Kint\Value\Representation\ContainerRepresentation;
use Kint\Value\Representation\ValueRepresentation;
use Kint\Value\UninitializedValue;
use mysqli_result;
use PDOStatement;
use SimpleXMLElement;
use SplFileObject;
use Throwable;
use Traversable;

class IteratorPlugin extends AbstractPlugin implements PluginCompleteInterface
{
    /**
     * List of classes and interfaces to blacklist.
     *
     * Certain classes (Such as PDOStatement) irreversibly lose information
     * when traversed. Others are just huge. Either way, put them in here
     * and you won't have to worry about them being parsed.
     *
     * @psalm-var class-string[]
     */
    public static array $blacklist = [
        NamedNodeMap::class,
        NodeList::class,
        DOMNamedNodeMap::class,
        DOMNodeList::class,
        mysqli_result::class,
        PDOStatement::class,
        SimpleXMLElement::class,
        SplFileObject::class,
    ];

    public function getTypes(): array
    {
        return ['object'];
    }

    public function getTriggers(): int
    {
        return Parser::TRIGGER_SUCCESS;
    }

    public function parseComplete(&$var, AbstractValue $v, int $trigger): AbstractValue
    {
        if (!$var instanceof Traversable || !$v instanceof InstanceValue || $v->getRepresentation('iterator')) {
            return $v;
        }

        $c = $v->getContext();

        foreach (self::$blacklist as $class) {
            /**
             * @psalm-suppress RedundantCondition
             * Psalm bug #11076
             */
            if ($var instanceof $class) {
                $base = new BaseContext($class.' Iterator Contents');
                $base->depth = $c->getDepth() + 1;
                if (null !== ($ap = $c->getAccessPath())) {
                    $base->access_path = 'iterator_to_array('.$ap.', false)';
                }

                $b = new UninitializedValue($base);
                $b->flags |= AbstractValue::FLAG_BLACKLIST;

                $v->addRepresentation(new ValueRepresentation('Iterator', $b));

                return $v;
            }
        }

        try {
            $data = \iterator_to_array($var, false);
        } catch (Throwable $t) {
            return $v;
        }

        if (!\count($data)) {
            return $v;
        }

        $base = new BaseContext('Iterator Contents');
        $base->depth = $c->getDepth();
        if (null !== ($ap = $c->getAccessPath())) {
            $base->access_path = 'iterator_to_array('.$ap.', false)';
        }

        $iter_val = $this->getParser()->parse($data, $base);

        // Since we didn't get TRIGGER_DEPTH_LIMIT and set the iterator to the
        // same depth we can assume at least 1 level deep will exist
        if ($iter_val instanceof ArrayValue && $iterator_items = $iter_val->getContents()) {
            $r = new ContainerRepresentation('Iterator', $iterator_items);
            $iterator_items = \array_values($iterator_items);
        } else {
            $r = new ValueRepresentation('Iterator', $iter_val);
            $iterator_items = [$iter_val];
        }

        if ((bool) $v->getChildren()) {
            $v->addRepresentation($r);
        } else {
            $v->setChildren($iterator_items);
            $v->addRepresentation($r, 0);
        }

        return $v;
    }
}
