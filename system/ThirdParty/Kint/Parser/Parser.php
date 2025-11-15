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

use DomainException;
use InvalidArgumentException;
use Kint\Utils;
use Kint\Value\AbstractValue;
use Kint\Value\ArrayValue;
use Kint\Value\ClosedResourceValue;
use Kint\Value\Context\ArrayContext;
use Kint\Value\Context\ClassDeclaredContext;
use Kint\Value\Context\ClassOwnedContext;
use Kint\Value\Context\ContextInterface;
use Kint\Value\Context\PropertyContext;
use Kint\Value\FixedWidthValue;
use Kint\Value\InstanceValue;
use Kint\Value\Representation\ContainerRepresentation;
use Kint\Value\Representation\StringRepresentation;
use Kint\Value\ResourceValue;
use Kint\Value\StringValue;
use Kint\Value\UninitializedValue;
use Kint\Value\UnknownValue;
use Kint\Value\VirtualValue;
use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;
use ReflectionReference;
use Throwable;

/**
 * @psalm-type ParserTrigger int-mask-of<Parser::TRIGGER_*>
 */
class Parser
{
    /**
     * Plugin triggers.
     *
     * These are constants indicating trigger points for plugins
     *
     * BEGIN: Before normal parsing
     * SUCCESS: After successful parsing
     * RECURSION: After parsing cancelled by recursion
     * DEPTH_LIMIT: After parsing cancelled by depth limit
     * COMPLETE: SUCCESS | RECURSION | DEPTH_LIMIT
     *
     * While a plugin's getTriggers may return any of these only one should
     * be given to the plugin when PluginInterface::parse is called
     */
    public const TRIGGER_NONE = 0;
    public const TRIGGER_BEGIN = 1 << 0;
    public const TRIGGER_SUCCESS = 1 << 1;
    public const TRIGGER_RECURSION = 1 << 2;
    public const TRIGGER_DEPTH_LIMIT = 1 << 3;
    public const TRIGGER_COMPLETE = self::TRIGGER_SUCCESS | self::TRIGGER_RECURSION | self::TRIGGER_DEPTH_LIMIT;

    /** @psalm-var ?class-string */
    protected ?string $caller_class;
    protected int $depth_limit = 0;
    protected array $array_ref_stack = [];
    protected array $object_hashes = [];
    protected array $plugins = [];

    /**
     * @param int     $depth_limit Maximum depth to parse data
     * @param ?string $caller      Caller class name
     *
     * @psalm-param ?class-string $caller
     */
    public function __construct(int $depth_limit = 0, ?string $caller = null)
    {
        $this->depth_limit = $depth_limit;
        $this->caller_class = $caller;
    }

    /**
     * Set the caller class.
     *
     * @psalm-param ?class-string $caller
     */
    public function setCallerClass(?string $caller = null): void
    {
        $this->noRecurseCall();

        $this->caller_class = $caller;
    }

    /** @psalm-return ?class-string */
    public function getCallerClass(): ?string
    {
        return $this->caller_class;
    }

    /**
     * Set the depth limit.
     *
     * @param int $depth_limit Maximum depth to parse data, 0 for none
     */
    public function setDepthLimit(int $depth_limit = 0): void
    {
        $this->noRecurseCall();

        $this->depth_limit = $depth_limit;
    }

    public function getDepthLimit(): int
    {
        return $this->depth_limit;
    }

    /**
     * Parses a variable into a Kint object structure.
     *
     * @param mixed &$var The input variable
     */
    public function parse(&$var, ContextInterface $c): AbstractValue
    {
        $type = \strtolower(\gettype($var));

        if ($v = $this->applyPluginsBegin($var, $c, $type)) {
            return $v;
        }

        switch ($type) {
            case 'array':
                return $this->parseArray($var, $c);
            case 'boolean':
            case 'double':
            case 'integer':
            case 'null':
                return $this->parseFixedWidth($var, $c);
            case 'object':
                return $this->parseObject($var, $c);
            case 'resource':
                return $this->parseResource($var, $c);
            case 'string':
                return $this->parseString($var, $c);
            case 'resource (closed)':
                return $this->parseResourceClosed($var, $c);

            case 'unknown type': // @codeCoverageIgnore
            default:
                // These should never happen. Unknown is resource (closed) from old
                // PHP versions and there shouldn't be any other types.
                return $this->parseUnknown($var, $c); // @codeCoverageIgnore
        }
    }

    public function addPlugin(PluginInterface $p): void
    {
        try {
            $this->noRecurseCall();
        } catch (DomainException $e) { // @codeCoverageIgnore
            \trigger_error('Calling Kint\\Parser::addPlugin from inside a parse is deprecated', E_USER_DEPRECATED); // @codeCoverageIgnore
        }

        if (!$types = $p->getTypes()) {
            return;
        }

        if (!$triggers = $p->getTriggers()) {
            return;
        }

        if ($triggers & self::TRIGGER_BEGIN && !$p instanceof PluginBeginInterface) {
            throw new InvalidArgumentException('Parsers triggered on begin must implement PluginBeginInterface');
        }

        if ($triggers & self::TRIGGER_COMPLETE && !$p instanceof PluginCompleteInterface) {
            throw new InvalidArgumentException('Parsers triggered on completion must implement PluginCompleteInterface');
        }

        $p->setParser($this);

        foreach ($types as $type) {
            $this->plugins[$type] ??= [
                self::TRIGGER_BEGIN => [],
                self::TRIGGER_SUCCESS => [],
                self::TRIGGER_RECURSION => [],
                self::TRIGGER_DEPTH_LIMIT => [],
            ];

            foreach ($this->plugins[$type] as $trigger => &$pool) {
                if ($triggers & $trigger) {
                    $pool[] = $p;
                }
            }
        }
    }

    public function clearPlugins(): void
    {
        try {
            $this->noRecurseCall();
        } catch (DomainException $e) { // @codeCoverageIgnore
            \trigger_error('Calling Kint\\Parser::clearPlugins from inside a parse is deprecated', E_USER_DEPRECATED); // @codeCoverageIgnore
        }

        $this->plugins = [];
    }

    protected function noRecurseCall(): void
    {
        $bt = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS);

        \reset($bt);
        /** @psalm-var class-string $caller_frame['class'] */
        $caller_frame = \next($bt);

        foreach ($bt as $frame) {
            if (isset($frame['object']) && $frame['object'] === $this && 'parse' === $frame['function']) {
                throw new DomainException($caller_frame['class'].'::'.$caller_frame['function'].' cannot be called from inside a parse');
            }
        }
    }

    /**
     * @psalm-param null|bool|float|int &$var
     */
    private function parseFixedWidth(&$var, ContextInterface $c): AbstractValue
    {
        $v = new FixedWidthValue($c, $var);

        return $this->applyPluginsComplete($var, $v, self::TRIGGER_SUCCESS);
    }

    private function parseString(string &$var, ContextInterface $c): AbstractValue
    {
        $string = new StringValue($c, $var, Utils::detectEncoding($var));

        if (false !== $string->getEncoding() && \strlen($var)) {
            $string->addRepresentation(new StringRepresentation('Contents', $var, null, true));
        }

        return $this->applyPluginsComplete($var, $string, self::TRIGGER_SUCCESS);
    }

    private function parseArray(array &$var, ContextInterface $c): AbstractValue
    {
        $size = \count($var);
        $contents = [];
        $parentRef = ReflectionReference::fromArrayElement([&$var], 0)->getId();

        if (isset($this->array_ref_stack[$parentRef])) {
            $array = new ArrayValue($c, $size, $contents);
            $array->flags |= AbstractValue::FLAG_RECURSION;

            return $this->applyPluginsComplete($var, $array, self::TRIGGER_RECURSION);
        }

        try {
            $this->array_ref_stack[$parentRef] = true;

            $cdepth = $c->getDepth();
            $ap = $c->getAccessPath();

            if ($size > 0 && $this->depth_limit && $cdepth >= $this->depth_limit) {
                $array = new ArrayValue($c, $size, $contents);
                $array->flags |= AbstractValue::FLAG_DEPTH_LIMIT;

                return $this->applyPluginsComplete($var, $array, self::TRIGGER_DEPTH_LIMIT);
            }

            foreach ($var as $key => $_) {
                $child = new ArrayContext($key);
                $child->depth = $cdepth + 1;
                $child->reference = null !== ReflectionReference::fromArrayElement($var, $key);

                if (null !== $ap) {
                    $child->access_path = $ap.'['.\var_export($key, true).']';
                }

                $contents[$key] = $this->parse($var[$key], $child);
            }

            $array = new ArrayValue($c, $size, $contents);

            if ($contents) {
                $array->addRepresentation(new ContainerRepresentation('Contents', $contents, null, true));
            }

            return $this->applyPluginsComplete($var, $array, self::TRIGGER_SUCCESS);
        } finally {
            unset($this->array_ref_stack[$parentRef]);
        }
    }

    /**
     * @psalm-return ReflectionProperty[]
     */
    private function getPropsOrdered(ReflectionClass $r): array
    {
        if ($parent = $r->getParentClass()) {
            $props = self::getPropsOrdered($parent);
        } else {
            $props = [];
        }

        foreach ($r->getProperties() as $prop) {
            if ($prop->isStatic()) {
                continue;
            }

            if ($prop->isPrivate()) {
                $props[] = $prop;
            } else {
                $props[$prop->name] = $prop;
            }
        }

        return $props;
    }

    /**
     * @codeCoverageIgnore
     *
     * @psalm-return ReflectionProperty[]
     */
    private function getPropsOrderedOld(ReflectionClass $r): array
    {
        $props = [];

        foreach ($r->getProperties() as $prop) {
            if ($prop->isStatic()) {
                continue;
            }

            $props[] = $prop;
        }

        while ($r = $r->getParentClass()) {
            foreach ($r->getProperties(ReflectionProperty::IS_PRIVATE) as $prop) {
                if ($prop->isStatic()) {
                    continue;
                }

                $props[] = $prop;
            }
        }

        return $props;
    }

    private function parseObject(object &$var, ContextInterface $c): AbstractValue
    {
        $hash = \spl_object_hash($var);
        $classname = \get_class($var);

        if (isset($this->object_hashes[$hash])) {
            $object = new InstanceValue($c, $classname, $hash, \spl_object_id($var));
            $object->flags |= AbstractValue::FLAG_RECURSION;

            return $this->applyPluginsComplete($var, $object, self::TRIGGER_RECURSION);
        }

        try {
            $this->object_hashes[$hash] = true;

            $cdepth = $c->getDepth();
            $ap = $c->getAccessPath();

            if ($this->depth_limit && $cdepth >= $this->depth_limit) {
                $object = new InstanceValue($c, $classname, $hash, \spl_object_id($var));
                $object->flags |= AbstractValue::FLAG_DEPTH_LIMIT;

                return $this->applyPluginsComplete($var, $object, self::TRIGGER_DEPTH_LIMIT);
            }

            if (KINT_PHP81) {
                $props = $this->getPropsOrdered(new ReflectionObject($var));
            } else {
                $props = $this->getPropsOrderedOld(new ReflectionObject($var)); // @codeCoverageIgnore
            }

            $values = (array) $var;
            $properties = [];

            foreach ($props as $rprop) {
                $rprop->setAccessible(true);
                $name = $rprop->getName();

                // Casting object to array:
                // private properties show in the form "\0$owner_class_name\0$property_name";
                // protected properties show in the form "\0*\0$property_name";
                // public properties show in the form "$property_name";
                // http://www.php.net/manual/en/language.types.array.php#language.types.array.casting
                $key = $name;
                if ($rprop->isProtected()) {
                    $key = "\0*\0".$name;
                } elseif ($rprop->isPrivate()) {
                    $key = "\0".$rprop->getDeclaringClass()->getName()."\0".$name;
                }
                $initialized = \array_key_exists($key, $values);
                if ($key === (string) (int) $key) {
                    $key = (int) $key;
                }

                if ($rprop->isDefault()) {
                    $child = new PropertyContext(
                        $name,
                        $rprop->getDeclaringClass()->getName(),
                        ClassDeclaredContext::ACCESS_PUBLIC
                    );

                    $child->readonly = KINT_PHP81 && $rprop->isReadOnly();

                    if ($rprop->isProtected()) {
                        $child->access = ClassDeclaredContext::ACCESS_PROTECTED;
                    } elseif ($rprop->isPrivate()) {
                        $child->access = ClassDeclaredContext::ACCESS_PRIVATE;
                    }

                    if (KINT_PHP84) {
                        if ($rprop->isProtectedSet()) {
                            $child->access_set = ClassDeclaredContext::ACCESS_PROTECTED;
                        } elseif ($rprop->isPrivateSet()) {
                            $child->access_set = ClassDeclaredContext::ACCESS_PRIVATE;
                        }

                        $hooks = $rprop->getHooks();
                        if (isset($hooks['get'])) {
                            $child->hooks |= PropertyContext::HOOK_GET;
                            if ($hooks['get']->returnsReference()) {
                                $child->hooks |= PropertyContext::HOOK_GET_REF;
                            }
                        }
                        if (isset($hooks['set'])) {
                            $child->hooks |= PropertyContext::HOOK_SET;

                            $child->hook_set_type = (string) $rprop->getSettableType();
                            if ($child->hook_set_type !== (string) $rprop->getType()) {
                                $child->hooks |= PropertyContext::HOOK_SET_TYPE;
                            } elseif ('' === $child->hook_set_type) {
                                $child->hook_set_type = null;
                            }
                        }
                    }
                } else {
                    $child = new ClassOwnedContext($name, $rprop->getDeclaringClass()->getName());
                }

                $child->reference = $initialized && null !== ReflectionReference::fromArrayElement($values, $key);
                $child->depth = $cdepth + 1;

                if (null !== $ap && $child->isAccessible($this->caller_class)) {
                    /** @psalm-var string $child->name */
                    if (Utils::isValidPhpName($child->name)) {
                        $child->access_path = $ap.'->'.$child->name;
                    } else {
                        $child->access_path = $ap.'->{'.\var_export($child->name, true).'}';
                    }
                }

                if (KINT_PHP84 && $rprop->isVirtual()) {
                    $properties[] = new VirtualValue($child);
                } elseif (!$initialized) {
                    $properties[] = new UninitializedValue($child);
                } else {
                    $properties[] = $this->parse($values[$key], $child);
                }
            }

            $object = new InstanceValue($c, $classname, $hash, \spl_object_id($var));
            if ($props) {
                $object->setChildren($properties);
            }

            if ($properties) {
                $object->addRepresentation(new ContainerRepresentation('Properties', $properties));
            }

            return $this->applyPluginsComplete($var, $object, self::TRIGGER_SUCCESS);
        } finally {
            unset($this->object_hashes[$hash]);
        }
    }

    /**
     * @psalm-param resource $var
     */
    private function parseResource(&$var, ContextInterface $c): AbstractValue
    {
        $resource = new ResourceValue($c, \get_resource_type($var));

        $resource = $this->applyPluginsComplete($var, $resource, self::TRIGGER_SUCCESS);

        return $resource;
    }

    /**
     * @psalm-param mixed $var
     */
    private function parseResourceClosed(&$var, ContextInterface $c): AbstractValue
    {
        $v = new ClosedResourceValue($c);

        $v = $this->applyPluginsComplete($var, $v, self::TRIGGER_SUCCESS);

        return $v;
    }

    /**
     * Catch-all for any unexpectedgettype.
     *
     * This should never happen.
     *
     * @codeCoverageIgnore
     *
     * @psalm-param mixed $var
     */
    private function parseUnknown(&$var, ContextInterface $c): AbstractValue
    {
        $v = new UnknownValue($c);

        $v = $this->applyPluginsComplete($var, $v, self::TRIGGER_SUCCESS);

        return $v;
    }

    /**
     * Applies plugins for a yet-unparsed value.
     *
     * @param mixed &$var The input variable
     */
    private function applyPluginsBegin(&$var, ContextInterface $c, string $type): ?AbstractValue
    {
        $plugins = $this->plugins[$type][self::TRIGGER_BEGIN] ?? [];

        foreach ($plugins as $plugin) {
            try {
                if ($v = $plugin->parseBegin($var, $c)) {
                    return $v;
                }
            } catch (Throwable $e) {
                \trigger_error(
                    Utils::errorSanitizeString(\get_class($e)).' was thrown in '.$e->getFile().' on line '.$e->getLine().' while executing '.Utils::errorSanitizeString(\get_class($plugin)).'->parseBegin. Error message: '.Utils::errorSanitizeString($e->getMessage()),
                    E_USER_WARNING
                );
            }
        }

        return null;
    }

    /**
     * Applies plugins for a parsed AbstractValue.
     *
     * @param mixed &$var The input variable
     */
    private function applyPluginsComplete(&$var, AbstractValue $v, int $trigger): AbstractValue
    {
        $plugins = $this->plugins[$v->getType()][$trigger] ?? [];

        foreach ($plugins as $plugin) {
            try {
                $v = $plugin->parseComplete($var, $v, $trigger);
            } catch (Throwable $e) {
                \trigger_error(
                    Utils::errorSanitizeString(\get_class($e)).' was thrown in '.$e->getFile().' on line '.$e->getLine().' while executing '.Utils::errorSanitizeString(\get_class($plugin)).'->parseComplete. Error message: '.Utils::errorSanitizeString($e->getMessage()),
                    E_USER_WARNING
                );
            }
        }

        return $v;
    }
}
