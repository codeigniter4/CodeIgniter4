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

use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class PlaceholderSampleGeneratorTest extends CIUnitTestCase
{
    #[DataProvider('provideGenerateProducesMatchingSample')]
    public function testGenerateProducesMatchingSample(string $pattern, string $expected): void
    {
        $generator = new PlaceholderSampleGenerator();

        $sample = $generator->generate($pattern);

        $this->assertSame($expected, $sample);
        $this->assertMatchesRegularExpression('#^(?:' . $pattern . ')$#', $sample);
    }

    /**
     * @return iterable<string, array{0: string, 1: string}>
     */
    public static function provideGenerateProducesMatchingSample(): iterable
    {
        yield 'digit class' => ['[0-9]+', '0'];

        yield 'uppercase class' => ['[A-Z]+', 'A'];

        yield 'mixed class prefers letter' => ['[A-Z0-9]+', 'A'];

        yield 'code pattern from issue #9804' => ['[A-Z]{3}[0-9]+', 'AAA0'];

        yield 'fixed quantifier' => ['[a-z]{5}', 'aaaaa'];

        yield 'range quantifier uses min' => ['[a-z]{2,5}', 'aa'];

        yield 'star quantifier collapses' => ['[a-z]*abc', 'abc'];

        yield 'optional collapses' => ['a?bc', 'bc'];

        yield 'literal passthrough' => ['login', 'login'];

        yield 'escape backslash digit' => ['\\d{4}', '0000'];

        yield 'escape backslash word' => ['\\w+', 'a'];

        yield 'escape backslash non-digit' => ['\\D+', 'a'];

        yield 'escape backslash non-word' => ['\\W+', '-'];

        yield 'escape backslash whitespace' => ['\\s+', ' '];

        yield 'escape backslash non-whitespace' => ['\\S+', 'a'];

        yield 'uuid (letters preferred)' => [
            '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}',
            'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
        ];

        yield 'anchored pattern tolerated' => ['^abc$', 'abc'];

        yield 'alternation picks first' => ['(?:foo|bar)', 'foo'];

        yield 'non-capturing group' => ['(?:x)+', 'x'];

        yield 'escape in class' => ['[A-Z\\-]+', 'A'];

        yield 'hyphen at end of class' => ['[A-Z-]+', 'A'];

        yield 'negated class avoids forbidden digits' => ['[^0-9]+', 'a'];

        yield 'negated class avoids forbidden letter' => ['[^a]+', 'A'];

        yield 'escaped digit in class' => ['[\\d]+', '0'];

        yield 'escaped word in class' => ['[\\w]+', 'a'];

        yield 'escaped whitespace in class' => ['[\\s]+', ' '];

        yield 'punctuation-only class' => ['[._-]+', '.'];

        yield 'non-greedy star' => ['a*?', ''];

        yield 'non-greedy plus' => ['a+?', 'a'];

        yield 'possessive plus' => ['a++', 'a'];

        yield 'possessive star collapses' => ['a*+', ''];

        yield 'top-level alternation' => ['a|b', 'a'];

        yield 'top-level multi-branch alternation' => ['cat|dog|fish', 'cat'];

        yield 'escaped literal outside class' => ['a\\.b', 'a.b'];

        yield 'open-ended brace quantifier' => ['[a-z]{2,}', 'aa'];

        yield 'literal bracket first in class' => ['[]]', ']'];

        yield 'negated class with bracket first' => ['[^]]', 'a'];

        yield 'negated class forces digit' => ['[^a-zA-Z]+', '0'];

        yield 'empty pattern' => ['', ''];
    }

    #[DataProvider('provideGenerateReturnsNullForUnsupportedPatterns')]
    public function testGenerateReturnsNullForUnsupportedPatterns(string $pattern): void
    {
        $generator = new PlaceholderSampleGenerator();

        $this->assertNull($generator->generate($pattern));
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function provideGenerateReturnsNullForUnsupportedPatterns(): iterable
    {
        yield 'lookahead' => ['(?=abc)abc'];

        yield 'lookbehind' => ['(?<=abc)def'];

        yield 'backreference' => ['(foo)\\1'];

        yield 'named group' => ['(?P<name>abc)'];

        yield 'negated class in class' => ['[\\D]+'];

        yield 'unclosed class' => ['[abc'];

        yield 'reversed range' => ['[z-a]'];

        yield 'dangling brace' => ['a{2'];

        yield 'stray closing paren' => ['abc)def'];

        yield 'metachar at atom position' => ['+abc'];

        yield 'trailing backslash' => ['abc\\'];

        yield 'unclosed escape in class' => ['[\\'];

        yield 'unclosed group' => ['(abc'];

        yield 'non-numeric brace body' => ['a{foo}'];

        yield 'negated class exhausts candidates' => ['[^aA0_-]'];

        yield 'hash breaks validation delimiter' => ['[A-Z]#[0-9]+'];
    }

    public function testGenerateValidatesAgainstOriginalPattern(): void
    {
        $generator = new PlaceholderSampleGenerator();

        // The dot atom emits 'a', which matches '.'. This sanity-checks the guard.
        $this->assertSame('a', $generator->generate('.'));
    }

    public function testGenerateReturnsEmptyStringForOptionalOnlyPattern(): void
    {
        $generator = new PlaceholderSampleGenerator();

        $this->assertSame('', $generator->generate('[a-z]?'));
    }
}
