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

namespace CodeIgniter\Commands\Utilities\Routes;

/**
 * Generates a sample string that matches a simple regular expression pattern.
 *
 * Supports the common fragments seen in custom route placeholders: character
 * classes (``[A-Z]``, ``[0-9a-f]``), shorthand escapes (``\d``, ``\w``),
 * quantifiers (``{n}``, ``{n,m}``, ``+``, ``*``, ``?``), literals, and
 * top-level alternation (``a|b``). Anchors (``^``, ``$``) are tolerated and
 * ignored. Anything more exotic (lookarounds, backreferences, nested groups
 * with quantifiers, POSIX classes) causes the generator to bail out by
 * returning ``null``.
 *
 * The generated candidate is validated against the original pattern with
 * ``preg_match`` before being returned, so callers never receive a non-matching
 * sample even when the parser is too permissive for a given edge case.
 *
 * @see \CodeIgniter\Commands\Utilities\Routes\PlaceholderSampleGeneratorTest
 */
final class PlaceholderSampleGenerator
{
    private string $pattern;
    private int $position = 0;
    private int $length   = 0;

    /**
     * Returns a sample string matching the regular expression ``$pattern``, or
     * ``null`` when the pattern uses features this generator cannot reverse.
     */
    public function generate(string $pattern): ?string
    {
        $this->pattern  = $pattern;
        $this->position = 0;
        $this->length   = strlen($pattern);

        try {
            $sample = $this->parseAlternation();
        } catch (UnsupportedPatternException) {
            return null;
        }

        if ($this->position !== $this->length) {
            return null;
        }

        if (@preg_match('#^(?:' . $pattern . ')$#', $sample) !== 1) {
            return null;
        }

        return $sample;
    }

    /**
     * Parses a (possibly alternated) sequence and returns the first branch.
     */
    private function parseAlternation(): string
    {
        $branch = $this->parseSequence();

        if ($this->position < $this->length && $this->pattern[$this->position] === '|') {
            // Skip the remaining branches; the first one is enough.
            while ($this->position < $this->length && $this->pattern[$this->position] === '|') {
                $this->position++;
                $this->parseSequence();
            }
        }

        return $branch;
    }

    private function parseSequence(): string
    {
        $output = '';

        while ($this->position < $this->length) {
            $char = $this->pattern[$this->position];

            if ($char === '|' || $char === ')') {
                break;
            }

            $atom = $this->parseAtom();

            [$minRepeat] = $this->parseQuantifier();

            $output .= str_repeat($atom, $minRepeat);
        }

        return $output;
    }

    private function parseAtom(): string
    {
        $char = $this->pattern[$this->position];

        if ($char === '^' || $char === '$') {
            $this->position++;

            return '';
        }

        if ($char === '.') {
            $this->position++;

            return 'a';
        }

        if ($char === '\\') {
            return $this->parseEscape();
        }

        if ($char === '[') {
            return $this->parseCharacterClass();
        }

        if ($char === '(') {
            return $this->parseGroup();
        }

        // Unsupported metacharacters outside of the ones handled above.
        if (in_array($char, ['+', '*', '?', '{', '}', ']'], true)) {
            throw new UnsupportedPatternException();
        }

        $this->position++;

        return $char;
    }

    private function parseEscape(): string
    {
        $this->position++;

        if ($this->position >= $this->length) {
            throw new UnsupportedPatternException();
        }

        $char = $this->pattern[$this->position];
        $this->position++;

        return match ($char) {
            'd'     => '0',
            'D'     => 'a',
            'w'     => 'a',
            'W'     => '-',
            's'     => ' ',
            'S'     => 'a',
            default => $char,
        };
    }

    private function parseCharacterClass(): string
    {
        $this->position++;
        $negated = false;

        if ($this->position < $this->length && $this->pattern[$this->position] === '^') {
            $negated = true;
            $this->position++;
        }

        $allowed = [];
        $first   = true;

        while ($this->position < $this->length && ($this->pattern[$this->position] !== ']' || $first)) {
            $first = false;
            $char  = $this->pattern[$this->position];

            if ($char === '\\') {
                $this->position++;

                if ($this->position >= $this->length) {
                    throw new UnsupportedPatternException();
                }

                $escaped = $this->pattern[$this->position];
                $this->position++;

                $allowed = [...$allowed, ...$this->expandEscapedClassChar($escaped)];

                continue;
            }

            // Range a-z
            if (
                $this->position < $this->length - 2
                && $this->pattern[$this->position + 1] === '-'
                && $this->pattern[$this->position + 2] !== ']'
            ) {
                $start = $char;

                $end = $this->pattern[$this->position + 2];
                $this->position += 3;

                if (ord($start) > ord($end)) {
                    throw new UnsupportedPatternException();
                }

                for ($code = ord($start); $code <= ord($end); $code++) {
                    $allowed[] = chr($code);
                }

                continue;
            }

            $allowed[] = $char;
            $this->position++;
        }

        if ($this->position >= $this->length || $this->pattern[$this->position] !== ']') {
            throw new UnsupportedPatternException();
        }

        $this->position++;

        return $negated ? $this->pickNegated($allowed) : $this->pickPreferred($allowed);
    }

    private function parseGroup(): string
    {
        $this->position++;

        // Skip non-capturing / named group prefixes (?:..), (?P<name>..), (?<name>..).
        if (
            $this->position < $this->length - 1
            && $this->pattern[$this->position] === '?'
            && $this->pattern[$this->position + 1] === ':'
        ) {
            $this->position += 2;
        } elseif (
            $this->position < $this->length
            && $this->pattern[$this->position] === '?'
        ) {
            // Lookarounds, named groups with special syntax, atomic groups, etc.
            throw new UnsupportedPatternException();
        }

        $inner = $this->parseAlternation();

        if ($this->position >= $this->length || $this->pattern[$this->position] !== ')') {
            throw new UnsupportedPatternException();
        }

        $this->position++;

        return $inner;
    }

    /**
     * Reads the quantifier following the current atom and returns ``[min, max]``.
     *
     * ``max`` is informational only; the generator always emits the minimum
     * number of repetitions (with ``*`` and ``?`` normalized to zero).
     *
     * @return array{0: int, 1: int|null}
     */
    private function parseQuantifier(): array
    {
        if ($this->position >= $this->length) {
            return [1, 1];
        }

        $char = $this->pattern[$this->position];

        $bounds = match ($char) {
            '?'     => [0, 1],
            '*'     => [0, null],
            '+'     => [1, null],
            default => null,
        };

        if ($bounds !== null) {
            $this->position++;
            $this->consumeGreedyModifier();

            return $bounds;
        }

        if ($char === '{') {
            return $this->parseBraceQuantifier();
        }

        return [1, 1];
    }

    /**
     * @return array{0: int, 1: int|null}
     */
    private function parseBraceQuantifier(): array
    {
        $end = strpos($this->pattern, '}', $this->position);
        if ($end === false) {
            throw new UnsupportedPatternException();
        }

        $body = substr($this->pattern, $this->position + 1, $end - $this->position - 1);

        $this->position = $end + 1;
        $this->consumeGreedyModifier();

        if (preg_match('/^(\d+)(?:,(\d*))?$/', $body, $matches) !== 1) {
            throw new UnsupportedPatternException();
        }

        $min = (int) $matches[1];
        $max = isset($matches[2]) && $matches[2] !== '' ? (int) $matches[2] : null;

        return [$min, $max];
    }

    private function consumeGreedyModifier(): void
    {
        if (
            $this->position < $this->length
            && ($this->pattern[$this->position] === '?' || $this->pattern[$this->position] === '+')
        ) {
            $this->position++;
        }
    }

    /**
     * Prefer letters, then digits, then anything printable. Keeps samples
     * readable (``[A-Z0-9]`` → ``A``, not ``0``).
     *
     * @param list<string> $chars
     */
    private function pickPreferred(array $chars): string
    {
        foreach (['/[A-Za-z]/', '/\d/', '/[^\s\/]/'] as $preference) {
            foreach ($chars as $c) {
                if (preg_match($preference, $c) === 1) {
                    return $c;
                }
            }
        }

        return $chars[0];
    }

    /**
     * @param list<string> $forbidden
     */
    private function pickNegated(array $forbidden): string
    {
        $lookup = array_flip($forbidden);

        foreach (['a', 'A', '0', '_', '-'] as $candidate) {
            if (! isset($lookup[$candidate])) {
                return $candidate;
            }
        }

        throw new UnsupportedPatternException();
    }

    /**
     * @return list<string>
     */
    private function expandEscapedClassChar(string $char): array
    {
        return match ($char) {
            'd'           => ['0'],
            'w'           => ['a'],
            's'           => [' '],
            'D', 'W', 'S' => throw new UnsupportedPatternException(),
            default       => [$char],
        };
    }
}
