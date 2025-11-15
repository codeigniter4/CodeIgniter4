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

use InvalidArgumentException;
use Kint\Utils;
use Kint\Value\AbstractValue;
use Kint\Value\ArrayValue;
use Kint\Value\Context\BaseContext;
use Kint\Value\Context\ContextInterface;
use Kint\Value\Representation\ContainerRepresentation;
use Kint\Value\Representation\ProfileRepresentation;
use Kint\Value\Representation\ValueRepresentation;

class ArrayLimitPlugin extends AbstractPlugin implements PluginBeginInterface
{
    /**
     * Maximum size of arrays before limiting.
     */
    public static int $trigger = 1000;

    /**
     * Maximum amount of items to show in a limited array.
     */
    public static int $limit = 50;

    /**
     * Don't limit arrays with string keys.
     */
    public static bool $numeric_only = true;

    public function __construct(Parser $p)
    {
        if (self::$limit < 0) {
            throw new InvalidArgumentException('ArrayLimitPlugin::$limit can not be lower than 0');
        }

        if (self::$limit >= self::$trigger) {
            throw new InvalidArgumentException('ArrayLimitPlugin::$limit can not be lower than ArrayLimitPlugin::$trigger');
        }

        parent::__construct($p);
    }

    public function getTypes(): array
    {
        return ['array'];
    }

    public function getTriggers(): int
    {
        return Parser::TRIGGER_BEGIN;
    }

    public function parseBegin(&$var, ContextInterface $c): ?AbstractValue
    {
        $parser = $this->getParser();
        $pdepth = $parser->getDepthLimit();

        if (!$pdepth) {
            return null;
        }

        $cdepth = $c->getDepth();

        if ($cdepth >= $pdepth - 1) {
            return null;
        }

        if (\count($var) < self::$trigger) {
            return null;
        }

        if (self::$numeric_only && Utils::isAssoc($var)) {
            return null;
        }

        $slice = \array_slice($var, 0, self::$limit, true);
        $array = $parser->parse($slice, $c);

        if (!$array instanceof ArrayValue) {
            return null;
        }

        $base = new BaseContext($c->getName());
        $base->depth = $pdepth - 1;
        $base->access_path = $c->getAccessPath();

        $slice = \array_slice($var, self::$limit, null, true);
        $slice = $parser->parse($slice, $base);

        if (!$slice instanceof ArrayValue) {
            return null;
        }

        foreach ($slice->getContents() as $child) {
            $this->replaceDepthLimit($child, $cdepth + 1);
        }

        $out = new ArrayValue($c, \count($var), \array_merge($array->getContents(), $slice->getContents()));
        $out->flags = $array->flags;

        // Explicitly copy over profile plugin
        $arrayp = $array->getRepresentation('profiling');
        $slicep = $slice->getRepresentation('profiling');
        if ($arrayp instanceof ProfileRepresentation && $slicep instanceof ProfileRepresentation) {
            $out->addRepresentation(new ProfileRepresentation($arrayp->complexity + $slicep->complexity));
        }

        // Add contents. Check is in case some bad plugin empties both $slice and $array
        if ($contents = $out->getContents()) {
            $out->addRepresentation(new ContainerRepresentation('Contents', $contents, null, true));
        }

        return $out;
    }

    protected function replaceDepthLimit(AbstractValue $v, int $depth): void
    {
        $c = $v->getContext();

        if ($c instanceof BaseContext) {
            $c->depth = $depth;
        }

        $pdepth = $this->getParser()->getDepthLimit();

        if (($v->flags & AbstractValue::FLAG_DEPTH_LIMIT) && $pdepth && $depth < $pdepth) {
            $v->flags = $v->flags & ~AbstractValue::FLAG_DEPTH_LIMIT | AbstractValue::FLAG_ARRAY_LIMIT;
        }

        $reps = $v->getRepresentations();

        foreach ($reps as $rep) {
            if ($rep instanceof ContainerRepresentation) {
                foreach ($rep->getContents() as $child) {
                    $this->replaceDepthLimit($child, $depth + 1);
                }
            } elseif ($rep instanceof ValueRepresentation) {
                $this->replaceDepthLimit($rep->getValue(), $depth + 1);
            }
        }
    }
}
