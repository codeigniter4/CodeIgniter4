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

namespace Kint;

/**
 * @psalm-type PhpTokenArray = array{int, string, int}
 * @psalm-type PhpToken = string|PhpTokenArray
 */
class CallFinder
{
    private static $ignore = [
        T_CLOSE_TAG => true,
        T_COMMENT => true,
        T_DOC_COMMENT => true,
        T_INLINE_HTML => true,
        T_OPEN_TAG => true,
        T_OPEN_TAG_WITH_ECHO => true,
        T_WHITESPACE => true,
    ];

    /**
     * Things we need to do specially for operator tokens:
     * - Refuse to strip spaces around them
     * - Wrap the access path in parentheses if there
     *   are any of these in the final short parameter.
     */
    private static $operator = [
        T_AND_EQUAL => true,
        T_BOOLEAN_AND => true,
        T_BOOLEAN_OR => true,
        T_ARRAY_CAST => true,
        T_BOOL_CAST => true,
        T_CLASS => true,
        T_CLONE => true,
        T_CONCAT_EQUAL => true,
        T_DEC => true,
        T_DIV_EQUAL => true,
        T_DOUBLE_CAST => true,
        T_FUNCTION => true,
        T_INC => true,
        T_INCLUDE => true,
        T_INCLUDE_ONCE => true,
        T_INSTANCEOF => true,
        T_INT_CAST => true,
        T_IS_EQUAL => true,
        T_IS_GREATER_OR_EQUAL => true,
        T_IS_IDENTICAL => true,
        T_IS_NOT_EQUAL => true,
        T_IS_NOT_IDENTICAL => true,
        T_IS_SMALLER_OR_EQUAL => true,
        T_LOGICAL_AND => true,
        T_LOGICAL_OR => true,
        T_LOGICAL_XOR => true,
        T_MINUS_EQUAL => true,
        T_MOD_EQUAL => true,
        T_MUL_EQUAL => true,
        T_NEW => true,
        T_OBJECT_CAST => true,
        T_OR_EQUAL => true,
        T_PLUS_EQUAL => true,
        T_REQUIRE => true,
        T_REQUIRE_ONCE => true,
        T_SL => true,
        T_SL_EQUAL => true,
        T_SR => true,
        T_SR_EQUAL => true,
        T_STRING_CAST => true,
        T_UNSET_CAST => true,
        T_XOR_EQUAL => true,
        T_POW => true,
        T_POW_EQUAL => true,
        T_SPACESHIP => true,
        T_DOUBLE_ARROW => true,
        '!' => true,
        '%' => true,
        '&' => true,
        '*' => true,
        '+' => true,
        '-' => true,
        '.' => true,
        '/' => true,
        ':' => true,
        '<' => true,
        '=' => true,
        '>' => true,
        '?' => true,
        '^' => true,
        '|' => true,
        '~' => true,
    ];

    private static $strip = [
        '(' => true,
        ')' => true,
        '[' => true,
        ']' => true,
        '{' => true,
        '}' => true,
        T_OBJECT_OPERATOR => true,
        T_DOUBLE_COLON => true,
        T_NS_SEPARATOR => true,
    ];

    private static $classcalls = [
        T_DOUBLE_COLON => true,
        T_OBJECT_OPERATOR => true,
    ];

    private static $namespace = [
        T_STRING => true,
    ];

    /**
     * @psalm-param callable-array|callable-string $function
     *
     * @param mixed $function
     *
     * @return array List of matching calls on the relevant line
     */
    public static function getFunctionCalls(string $source, int $line, $function): array
    {
        static $up = [
            '(' => true,
            '[' => true,
            '{' => true,
            T_CURLY_OPEN => true,
            T_DOLLAR_OPEN_CURLY_BRACES => true,
        ];
        static $down = [
            ')' => true,
            ']' => true,
            '}' => true,
        ];
        static $modifiers = [
            '!' => true,
            '@' => true,
            '~' => true,
            '+' => true,
            '-' => true,
        ];
        static $identifier = [
            T_DOUBLE_COLON => true,
            T_STRING => true,
            T_NS_SEPARATOR => true,
        ];

        if (KINT_PHP74) {
            self::$operator[T_FN] = true;
            self::$operator[T_COALESCE_EQUAL] = true;
        }

        if (KINT_PHP80) {
            $up[T_ATTRIBUTE] = true;
            self::$operator[T_MATCH] = true;
            self::$strip[T_NULLSAFE_OBJECT_OPERATOR] = true;
            self::$classcalls[T_NULLSAFE_OBJECT_OPERATOR] = true;
            self::$namespace[T_NAME_FULLY_QUALIFIED] = true;
            self::$namespace[T_NAME_QUALIFIED] = true;
            self::$namespace[T_NAME_RELATIVE] = true;
            $identifier[T_NAME_FULLY_QUALIFIED] = true;
            $identifier[T_NAME_QUALIFIED] = true;
            $identifier[T_NAME_RELATIVE] = true;
        }

        $tokens = \token_get_all($source);
        $cursor = 1;
        $function_calls = [];

        // Performance optimization preventing backwards loops
        /** @psalm-var array<PhpToken|null> */
        $prev_tokens = [null, null, null];

        if (\is_array($function)) {
            $class = \explode('\\', $function[0]);
            $class = \strtolower(\end($class));
            $function = \strtolower($function[1]);
        } else {
            $class = null;
            /**
             * @psalm-suppress RedundantFunctionCallGivenDocblockType
             */
            $function = \strtolower($function);
        }

        // Loop through tokens
        foreach ($tokens as $index => $token) {
            if (!\is_array($token)) {
                continue;
            }

            // Count newlines for line number instead of using $token[2]
            // since certain situations (String tokens after whitespace) may
            // not have the correct line number unless you do this manually
            $cursor += \substr_count($token[1], "\n");
            if ($cursor > $line) {
                break;
            }

            // Store the last real tokens for later
            if (isset(self::$ignore[$token[0]])) {
                continue;
            }

            $prev_tokens = [$prev_tokens[1], $prev_tokens[2], $token];

            // Check if it's the right type to be the function we're looking for
            if (!isset(self::$namespace[$token[0]])) {
                continue;
            }

            $ns = \explode('\\', \strtolower($token[1]));

            if (\end($ns) !== $function) {
                continue;
            }

            // Check if it's a function call
            $nextReal = self::realTokenIndex($tokens, $index);
            if (!isset($nextReal, $tokens[$nextReal]) || '(' !== $tokens[$nextReal]) {
                continue;
            }

            // Check if it matches the signature
            if (null === $class) {
                if ($prev_tokens[1] && isset(self::$classcalls[$prev_tokens[1][0]])) {
                    continue;
                }
            } else {
                if (!$prev_tokens[1] || T_DOUBLE_COLON !== $prev_tokens[1][0]) {
                    continue;
                }

                if (!$prev_tokens[0] || !isset(self::$namespace[$prev_tokens[0][0]])) {
                    continue;
                }

                // All self::$namespace tokens are T_ constants
                /** @psalm-var PhpTokenArray $prev_tokens[0] */
                $ns = \explode('\\', \strtolower($prev_tokens[0][1]));

                if (\end($ns) !== $class) {
                    continue;
                }
            }

            $inner_cursor = $cursor;
            $depth = 1; // The depth respective to the function call
            $offset = $nextReal + 1; // The start of the function call
            $instring = false; // Whether we're in a string or not
            $realtokens = false; // Whether the current scope contains anything meaningful or not
            $paramrealtokens = false; // Whether the current parameter contains anything meaningful
            $params = []; // All our collected parameters
            $shortparam = []; // The short version of the parameter
            $param_start = $offset; // The distance to the start of the parameter

            // Loop through the following tokens until the function call ends
            while (isset($tokens[$offset])) {
                $token = $tokens[$offset];

                // Ensure that the $inner_cursor is correct and
                // that $token is either a T_ constant or a string
                if (\is_array($token)) {
                    $inner_cursor += \substr_count($token[1], "\n");
                }

                if (!isset(self::$ignore[$token[0]]) && !isset($down[$token[0]])) {
                    $paramrealtokens = $realtokens = true;
                }

                // If it's a token that makes us to up a level, increase the depth
                if (isset($up[$token[0]])) {
                    if (1 === $depth) {
                        $shortparam[] = $token;
                        $realtokens = false;
                    }

                    ++$depth;
                } elseif (isset($down[$token[0]])) {
                    --$depth;

                    // If this brings us down to the parameter level, and we've had
                    // real tokens since going up, fill the $shortparam with an ellipsis
                    if (1 === $depth) {
                        if ($realtokens) {
                            $shortparam[] = '...';
                        }
                        $shortparam[] = $token;
                    }
                } elseif ('"' === $token[0]) {
                    // Strings use the same symbol for up and down, but we can
                    // only ever be inside one string, so just use a bool for that
                    if ($instring) {
                        --$depth;
                        if (1 === $depth) {
                            $shortparam[] = '...';
                        }
                    } else {
                        ++$depth;
                    }

                    $instring = !$instring;

                    $shortparam[] = '"';
                } elseif (1 === $depth) {
                    if (',' === $token[0]) {
                        $params[] = [
                            'full' => \array_slice($tokens, $param_start, $offset - $param_start),
                            'short' => $shortparam,
                        ];
                        $shortparam = [];
                        $paramrealtokens = false;
                        $param_start = $offset + 1;
                    } elseif (T_CONSTANT_ENCAPSED_STRING === $token[0] && \strlen($token[1]) > 2) {
                        $shortparam[] = $token[1][0].'...'.$token[1][0];
                    } else {
                        $shortparam[] = $token;
                    }
                }

                // Depth has dropped to 0 (So we've hit the closing paren)
                if ($depth <= 0) {
                    if ($paramrealtokens) {
                        $params[] = [
                            'full' => \array_slice($tokens, $param_start, $offset - $param_start),
                            'short' => $shortparam,
                        ];
                    }

                    break;
                }

                ++$offset;
            }

            // If we're not passed (or at) the line at the end
            // of the function call, we're too early so skip it
            if ($inner_cursor < $line) {
                continue;
            }

            // Format the final output parameters
            foreach ($params as &$param) {
                $name = self::tokensFormatted($param['short']);

                $expression = false;
                foreach ($name as $token) {
                    if (self::tokenIsOperator($token)) {
                        $expression = true;
                        break;
                    }
                }

                $param = [
                    'name' => self::tokensToString($name),
                    'path' => self::tokensToString(self::tokensTrim($param['full'])),
                    'expression' => $expression,
                ];
            }

            // Skip first-class callables
            /** @psalm-var list<array{name: string, path: string, expression: bool}> $params */
            if (KINT_PHP81 && 1 === \count($params) && '...' === \reset($params)['path']) {
                continue;
            }

            // Get the modifiers
            --$index;

            while (isset($tokens[$index])) {
                if (!isset(self::$ignore[$tokens[$index][0]]) && !isset($identifier[$tokens[$index][0]])) {
                    break;
                }

                --$index;
            }

            $mods = [];

            while (isset($tokens[$index])) {
                if (isset(self::$ignore[$tokens[$index][0]])) {
                    --$index;
                    continue;
                }

                if (isset($modifiers[$tokens[$index][0]])) {
                    $mods[] = $tokens[$index];
                    --$index;
                    continue;
                }

                break;
            }

            $function_calls[] = [
                'parameters' => $params,
                'modifiers' => $mods,
            ];
        }

        return $function_calls;
    }

    /**
     * @psalm-param PhpToken[] $tokens
     */
    private static function realTokenIndex(array $tokens, int $index): ?int
    {
        ++$index;

        while (isset($tokens[$index])) {
            if (!isset(self::$ignore[$tokens[$index][0]])) {
                return $index;
            }

            ++$index;
        }

        return null;
    }

    /**
     * We need a separate method to check if tokens are operators because we
     * occasionally add "..." to short parameter versions. If we simply check
     * for `$token[0]` then "..." will incorrectly match the "." operator.
     *
     * @psalm-param PhpToken $token The token to check
     *
     * @param mixed $token
     */
    private static function tokenIsOperator($token): bool
    {
        return '...' !== $token && isset(self::$operator[$token[0]]);
    }

    /**
     * @psalm-param PhpToken[] $tokens
     */
    private static function tokensToString(array $tokens): string
    {
        $out = '';

        foreach ($tokens as $token) {
            if (\is_string($token)) {
                $out .= $token;
            } else {
                $out .= $token[1];
            }
        }

        return $out;
    }

    /**
     * @psalm-param PhpToken[] $tokens
     */
    private static function tokensTrim(array $tokens): array
    {
        foreach ($tokens as $index => $token) {
            if (isset(self::$ignore[$token[0]])) {
                unset($tokens[$index]);
            } else {
                break;
            }
        }

        $tokens = \array_reverse($tokens);

        foreach ($tokens as $index => $token) {
            if (isset(self::$ignore[$token[0]])) {
                unset($tokens[$index]);
            } else {
                break;
            }
        }

        return \array_reverse($tokens);
    }

    /**
     * @psalm-param PhpToken[] $tokens
     *
     * @psalm-return PhpToken[]
     */
    private static function tokensFormatted(array $tokens): array
    {
        $tokens = self::tokensTrim($tokens);

        $space = false;
        $attribute = false;
        // Keep space between "strip" symbols for different behavior for matches or closures
        // Normally we want to strip spaces between strip tokens: $x{...}[...]
        // However with closures and matches we don't: function (...) {...}
        $ignorestrip = false;
        $output = [];
        $last = null;

        if (T_FUNCTION === $tokens[0][0] ||
            (KINT_PHP74 && T_FN === $tokens[0][0]) ||
            (KINT_PHP80 && T_MATCH === $tokens[0][0])
        ) {
            $ignorestrip = true;
        }

        foreach ($tokens as $index => $token) {
            if (isset(self::$ignore[$token[0]])) {
                if ($space) {
                    continue;
                }

                $next = self::realTokenIndex($tokens, $index);
                if (null === $next) {
                    // This should be impossible, since we always call tokensTrim first
                    break; // @codeCoverageIgnore
                }
                $next = $tokens[$next];

                /** @psalm-var PhpToken $last */
                if ($attribute && ']' === $last[0]) {
                    $attribute = false;
                } elseif (!$ignorestrip && isset(self::$strip[$last[0]]) && !self::tokenIsOperator($next)) {
                    continue;
                }

                if (!$ignorestrip && isset(self::$strip[$next[0]]) && $last && !self::tokenIsOperator($last)) {
                    continue;
                }

                $token = ' ';
                $space = true;
            } else {
                if (KINT_PHP80 && $last && T_ATTRIBUTE == $last[0]) {
                    $attribute = true;
                }

                $space = false;
                $last = $token;
            }

            $output[] = $token;
        }

        return $output;
    }
}
