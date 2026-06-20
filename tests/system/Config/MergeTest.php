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

namespace CodeIgniter\Config;

use Closure;
use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;

/**
 * Exercises the merge engine (applyMerge()/mergeByKey()) directly, independent
 * of registrar discovery.
 *
 * @internal
 */
#[Group('Others')]
final class MergeTest extends CIUnitTestCase
{
    /**
     * @var Closure(mixed, Merge): mixed
     */
    private Closure $applyMerge;

    protected function setUp(): void
    {
        parent::setUp();

        // Build the config without running the constructor so registrar
        // discovery is never triggered for these pure engine tests.
        $config = (new ReflectionClass(BaseConfig::class))->newInstanceWithoutConstructor();

        $this->applyMerge = self::getPrivateMethodInvoker($config, 'applyMerge');
    }

    private function apply(mixed $current, Merge $directive): mixed
    {
        return ($this->applyMerge)($current, $directive);
    }

    public function testReplaceArray(): void
    {
        $this->assertSame(['c'], $this->apply(['a', 'b'], Merge::replace(['c'])));
    }

    public function testReplaceScalar(): void
    {
        $this->assertSame('redis', $this->apply('file', Merge::replace('redis')));
    }

    public function testReplaceBool(): void
    {
        $this->assertFalse($this->apply(true, Merge::replace(false)));
    }

    public function testReplaceNull(): void
    {
        $this->assertNull($this->apply('something', Merge::replace(null)));
    }

    public function testAppend(): void
    {
        $this->assertSame(['a', 'b', 'c'], $this->apply(['a', 'b'], Merge::append(['c'])));
    }

    public function testAppendOntoNonArrayCurrent(): void
    {
        $this->assertSame(['c'], $this->apply('scalar', Merge::append(['c'])));
        $this->assertSame(['c'], $this->apply(null, Merge::append(['c'])));
    }

    public function testAppendDeDups(): void
    {
        // A value already present is not duplicated; only the absent one is added.
        $this->assertSame(['a', 'b', 'c'], $this->apply(['a', 'b'], Merge::append(['b', 'c'])));
    }

    public function testAppendDeDupsWithinPayload(): void
    {
        // A value repeated inside the payload is added only once.
        $this->assertSame(['a', 'x'], $this->apply(['a'], Merge::append(['x', 'x'])));
    }

    public function testListOpsLeavePreExistingDuplicatesUntouched(): void
    {
        $this->assertSame(['a', 'a', 'b'], $this->apply(['a', 'a'], Merge::append(['b'])));
    }

    public function testPrepend(): void
    {
        $this->assertSame(['c', 'a', 'b'], $this->apply(['a', 'b'], Merge::prepend(['c'])));
    }

    public function testPrependDeDupsAndDoesNotMove(): void
    {
        // 'a' is already present, so it is left where it is, not moved to the front.
        $this->assertSame(['x', 'a', 'b'], $this->apply(['a', 'b'], Merge::prepend(['a', 'x'])));
    }

    public function testBeforeAnchorFound(): void
    {
        $base = ['csrf', 'invalidchars', 'toolbar'];
        $this->assertSame(
            ['csrf', 'invalidchars', 'auth', 'toolbar'],
            $this->apply($base, Merge::before('toolbar', ['auth'])),
        );
    }

    public function testAfterAnchorFound(): void
    {
        $base = ['csrf', 'invalidchars', 'toolbar'];
        $this->assertSame(
            ['csrf', 'auth', 'invalidchars', 'toolbar'],
            $this->apply($base, Merge::after('csrf', ['auth'])),
        );
    }

    public function testAfterMovesAnAlreadyPresentValue(): void
    {
        // auth exists before toolbar; after('toolbar', ['auth']) relocates it.
        $base = ['csrf', 'auth', 'toolbar'];
        $this->assertSame(
            ['csrf', 'toolbar', 'auth'],
            $this->apply($base, Merge::after('toolbar', ['auth'])),
        );
    }

    public function testBeforeMovesAnAlreadyPresentValue(): void
    {
        $base = ['csrf', 'auth', 'toolbar'];
        $this->assertSame(
            ['toolbar', 'csrf', 'auth'],
            $this->apply($base, Merge::before('csrf', ['toolbar'])),
        );
    }

    public function testAfterDeDupsWithinPayloadPreservingOrder(): void
    {
        // Repeated payload values collapse to first-seen order before insertion.
        $base = ['csrf', 'toolbar'];
        $this->assertSame(
            ['csrf', 'a', 'b', 'toolbar'],
            $this->apply($base, Merge::after('csrf', ['a', 'b', 'a'])),
        );
    }

    public function testAfterPerValueMix(): void
    {
        // auth present (moved), newFilter absent (inserted) - as one block after csrf.
        $base = ['csrf', 'auth', 'toolbar'];
        $this->assertSame(
            ['csrf', 'auth', 'newFilter', 'toolbar'],
            $this->apply($base, Merge::after('csrf', ['auth', 'newFilter'])),
        );
    }

    public function testAfterUsesFirstAnchorMatch(): void
    {
        $base = ['csrf', 'auth', 'csrf'];
        $this->assertSame(
            ['csrf', 'x', 'auth', 'csrf'],
            $this->apply($base, Merge::after('csrf', ['x'])),
        );
    }

    public function testAfterMissingAnchorFallsBackToAppend(): void
    {
        $base = ['csrf', 'auth'];
        $this->assertSame(
            ['csrf', 'auth', 'newFilter'],
            $this->apply($base, Merge::after('honeypot', ['newFilter'])),
        );
    }

    public function testBeforeMissingAnchorFallsBackToPrepend(): void
    {
        $base = ['csrf', 'auth'];
        $this->assertSame(
            ['newFilter', 'csrf', 'auth'],
            $this->apply($base, Merge::before('honeypot', ['newFilter'])),
        );
    }

    public function testMissingAnchorDoesNotRelocateAPresentValue(): void
    {
        // honeypot is absent and auth is already present → leave the list as-is.
        $base = ['csrf', 'auth', 'toolbar'];
        $this->assertSame($base, $this->apply($base, Merge::after('honeypot', ['auth'])));
    }

    public function testListOpOntoNonArrayCurrent(): void
    {
        $this->assertSame(['auth'], $this->apply(null, Merge::after('csrf', ['auth'])));
        $this->assertSame(['auth'], $this->apply('scalar', Merge::before('csrf', ['auth'])));
    }

    public function testRepeatedSameAnchorAfterLandsCloserToAnchor(): void
    {
        // Two registrars anchoring after('csrf', …) in turn: the later one lands
        // closer to the anchor (documented contract).
        $first  = $this->apply(['csrf'], Merge::after('csrf', ['a']));
        $second = $this->apply($first, Merge::after('csrf', ['b']));

        $this->assertSame(['csrf', 'b', 'a'], $second);
    }

    public function testRepeatedSameAnchorBeforeLandsCloserToAnchor(): void
    {
        $first  = $this->apply(['csrf'], Merge::before('csrf', ['a']));
        $second = $this->apply($first, Merge::before('csrf', ['b']));

        $this->assertSame(['a', 'b', 'csrf'], $second);
    }

    public function testBeforeRejectsAnchorInPayload(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Merge::before() cannot use a value that is also being inserted as its anchor.');

        Merge::before('csrf', ['csrf']);
    }

    public function testAfterRejectsAnchorInPayload(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Merge::after('csrf', ['x', 'csrf']);
    }

    public function testByKeyStringKeysRecurse(): void
    {
        // Example A - siblings preserved.
        $current = [
            'key1' => 'val1',
            'key2' => ['val2' => 'subVal2', 'val3' => 'subVal3'],
        ];
        $result = $this->apply($current, Merge::byKey([
            'key2' => ['val4' => 'subVal4'],
        ]));

        $this->assertSame([
            'key1' => 'val1',
            'key2' => ['val2' => 'subVal2', 'val3' => 'subVal3', 'val4' => 'subVal4'],
        ], $result);
    }

    public function testByKeyIntegerKeysAppend(): void
    {
        // Example B - Shield matrix superadmin list grows.
        $current = ['superadmin' => ['admin.access']];
        $result  = $this->apply($current, Merge::byKey([
            'superadmin' => ['shippinglabel-logos.*'],
        ]));

        $this->assertSame(['superadmin' => ['admin.access', 'shippinglabel-logos.*']], $result);
    }

    public function testByKeyScalarLeafReplace(): void
    {
        $this->assertSame(['x' => 2], $this->apply(['x' => 1], Merge::byKey(['x' => 2])));
    }

    public function testByKeyNestedDirectives(): void
    {
        // Example C - nested append()/replace() resolved, untouched sibling kept.
        $current = [
            'before' => ['csrf'],
            'after'  => ['toolbar'],
            'other'  => ['keep'],
        ];
        $result = $this->apply($current, Merge::byKey([
            'before' => Merge::append(['blogFilter']),
            'after'  => Merge::replace([]),
        ]));

        $this->assertSame([
            'before' => ['csrf', 'blogFilter'],
            'after'  => [],
            'other'  => ['keep'],
        ], $result);
    }

    public function testByKeyWithNestedOrderingDirectives(): void
    {
        // The realistic Filters case: order a filter relative to an existing one
        // inside the nested 'before'/'after' lists of 'globals'.
        $current = [
            'before' => ['csrf', 'invalidchars'],
            'after'  => ['toolbar'],
        ];
        $result = $this->apply($current, Merge::byKey([
            'before' => Merge::after('csrf', ['auth']),
            'after'  => Merge::prepend(['honeypot']),
        ]));

        $this->assertSame([
            'before' => ['csrf', 'auth', 'invalidchars'],
            'after'  => ['honeypot', 'toolbar'],
        ], $result);
    }

    public function testByKeyDirectiveInBrandNewSubtree(): void
    {
        // A directive under a key absent from the base resolves against an empty base.
        $result = $this->apply(['existing' => 1], Merge::byKey([
            'newKey' => Merge::append(['x']),
        ]));

        // The directive under the brand-new key is resolved, not stored literally.
        $this->assertSame(['existing' => 1, 'newKey' => ['x']], $result);
    }

    public function testByKeyDirectiveAtIntegerKeyAppends(): void
    {
        $result = $this->apply(['a'], Merge::byKey([
            Merge::replace('b'),
        ]));

        $this->assertSame(['a', 'b'], $result);
    }

    public function testByKeyResolvesBrandNewNestedArraySubtree(): void
    {
        // A string key missing from the base recurses with [] as its base.
        $result = $this->apply([], Merge::byKey([
            'deep' => ['nested' => ['value']],
        ]));

        $this->assertSame(['deep' => ['nested' => ['value']]], $result);
    }

    public function testAppendPayloadIsTerminalLiteral(): void
    {
        // A directive embedded in an append() payload is literal data, not interpreted.
        $result = $this->apply([], Merge::append([Merge::replace('x')]));

        $this->assertInstanceOf(Merge::class, $result[0]);
    }

    public function testReplacePayloadIsTerminalLiteral(): void
    {
        $payload = ['nested' => Merge::append(['x'])];
        $result  = $this->apply(['old'], Merge::replace($payload));

        $this->assertInstanceOf(Merge::class, $result['nested']);
    }

    public function testFactoriesSetStrategyAndValue(): void
    {
        $this->assertSame(Merge::REPLACE, Merge::replace('v')->strategy);
        $this->assertSame(Merge::APPEND, Merge::append(['v'])->strategy);
        $this->assertSame(Merge::PREPEND, Merge::prepend(['v'])->strategy);
        $this->assertSame(Merge::BEFORE, Merge::before('a', ['v'])->strategy);
        $this->assertSame(Merge::AFTER, Merge::after('a', ['v'])->strategy);
        $this->assertSame(Merge::BY_KEY, Merge::byKey(['v'])->strategy);
        $this->assertSame('v', Merge::replace('v')->value);
    }

    public function testFactoriesSetAnchor(): void
    {
        $this->assertSame('csrf', Merge::before('csrf', ['v'])->anchor);
        $this->assertSame('csrf', Merge::after('csrf', ['v'])->anchor);
        // Non-anchored directives carry a null anchor.
        $this->assertNull(Merge::append(['v'])->anchor);
    }
}
