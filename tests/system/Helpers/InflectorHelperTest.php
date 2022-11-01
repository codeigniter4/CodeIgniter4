<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Helpers;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 *
 * @group Others
 */
final class InflectorHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        helper('inflector');
    }

    public function testSingular()
    {
        $strings = [
            'matrices'  => 'matrix',
            'oxen'      => 'ox',
            'aliases'   => 'alias',
            'octupus'   => 'octupus',
            'shoes'     => 'shoe',
            'buses'     => 'bus',
            'campus'    => 'campus',
            'campuses'  => 'campus',
            'mice'      => 'mouse',
            'movies'    => 'movie',
            'series'    => 'series',
            'hives'     => 'hive',
            'lives'     => 'life',
            'analyses'  => 'analysis',
            'men'       => 'man',
            'people'    => 'person',
            'children'  => 'child',
            'statuses'  => 'status',
            'news'      => 'news',
            'us'        => 'us',
            'tests'     => 'test',
            'queries'   => 'query',
            'dogs'      => 'dog',
            'cats'      => 'cat',
            'families'  => 'family',
            'countries' => 'country',
        ];

        foreach ($strings as $pluralizedString => $singularizedString) {
            $singular = singular($pluralizedString);
            $this->assertSame($singular, $singularizedString);
        }
    }

    public function testPlural()
    {
        $strings = [
            'searches'  => 'search',
            'matrices'  => 'matrix',
            'oxen'      => 'ox',
            'aliases'   => 'alias',
            'octupus'   => 'octupus',
            'shoes'     => 'shoe',
            'buses'     => 'bus',
            'mice'      => 'mouse',
            'movies'    => 'movie',
            'series'    => 'series',
            'hives'     => 'hive',
            'lives'     => 'life',
            'analyses'  => 'analysis',
            'men'       => 'man',
            'people'    => 'person',
            'children'  => 'child',
            'statuses'  => 'status',
            'news'      => 'news',
            'us'        => 'us',
            'tests'     => 'test',
            'queries'   => 'query',
            'dogs'      => 'dog',
            'cats'      => 'cat',
            'families'  => 'family',
            'countries' => 'country',
        ];

        foreach ($strings as $pluralizedString => $singularizedString) {
            $plural = plural($singularizedString);
            $this->assertSame($plural, $pluralizedString);
        }
    }

    public function testCounted()
    {
        $triplets = [
            [
                3,
                'cat',
                '3 cats',
            ],
            [
                1,
                'cat',
                '1 cat',
            ],
            [
                0,
                'cat',
                '0 cats',
            ],
            [
                3,
                'cats',
                '3 cats',
            ],
            [
                1,
                'cats',
                '1 cat',
            ],
            [
                0,
                'cats',
                '0 cats',
            ],
            [
                3,
                'fish',
                '3 fish',
            ],
            [
                1,
                'fish',
                '1 fish',
            ],
            [
                0,
                'fish',
                '0 fish',
            ],
        ];

        foreach ($triplets as $triplet) {
            $result = counted($triplet[0], $triplet[1]);
            $this->assertSame($triplet[2], $result);
        }
    }

    public function testCamelize()
    {
        $strings = [
            'hello from codeIgniter 4' => 'helloFromCodeIgniter4',
            'hello_world'              => 'helloWorld',
        ];

        foreach ($strings as $lowerCasedString => $camelizedString) {
            $camelized = camelize($lowerCasedString);
            $this->assertSame($camelized, $camelizedString);
        }
    }

    public function testPascalize()
    {
        $strings = [
            'hello from codeIgniter 4' => 'HelloFromCodeIgniter4',
            'hello_world'              => 'HelloWorld',
        ];

        foreach ($strings as $lowerCasedString => $pascalizedString) {
            $pascalized = pascalize($lowerCasedString);
            $this->assertSame($pascalized, $pascalizedString);
        }
    }

    public function testUnderscore()
    {
        $strings = [
            'Hello From CodeIgniter 4' => 'Hello_From_CodeIgniter_4',
            'hello world'              => 'hello_world',
        ];

        foreach ($strings as $spaced => $underscore) {
            $underscored = underscore($spaced);
            $this->assertSame($underscored, $underscore);
        }
    }

    public function testHumanize()
    {
        $underscored = [
            'Hello_From_CodeIgniter_4',
            'Hello From CodeIgniter 4',
        ];
        $dashed = [
            'hello-world',
            'Hello World',
        ];

        $humanizedUnderscore = humanize($underscored[0]);
        $humanizedDash       = humanize($dashed[0], '-');

        $this->assertSame($humanizedUnderscore, $underscored[1]);
        $this->assertSame($humanizedDash, $dashed[1]);
    }

    public function testIsCountable()
    {
        $words = [
            'tip'        => 'advice',
            'fight'      => 'bravery',
            'thing'      => 'equipment',
            'deocration' => 'jewelry',
            'line'       => 'series',
            'letter'     => 'spelling',
        ];

        foreach ($words as $countable => $unCountable) {
            $this->assertTrue(is_pluralizable($countable));
            $this->assertFalse(is_pluralizable($unCountable));
        }
    }

    public function testDasherize()
    {
        $strings = [
            'hello_world'              => 'hello-world',
            'Hello_From_CodeIgniter_4' => 'Hello-From-CodeIgniter-4',
        ];

        foreach ($strings as $underscored => $dashed) {
            $dasherized = dasherize($underscored);
            $this->assertSame($dasherized, $dashed);
        }
    }

    public function provideOrdinal()
    {
        return [
            ['st', 1],
            ['nd', 2],
            ['rd', 3],
            ['th', 4],
            ['th', 11],
            ['th', 20],
            ['st', 21],
            ['nd', 22],
            ['rd', 23],
            ['th', 24],
        ];
    }

    /**
     * @dataProvider provideOrdinal
     */
    public function testOrdinal(string $suffix, int $number)
    {
        $this->assertSame($suffix, ordinal($number));
    }

    public function testOrdinalize()
    {
        $suffixedNumbers = [
            '1st'  => 1,
            '2nd'  => 2,
            '3rd'  => 3,
            '4th'  => 4,
            '11th' => 11,
            '20th' => 20,
            '21st' => 21,
            '22nd' => 22,
            '23rd' => 23,
            '24th' => 24,
        ];

        foreach ($suffixedNumbers as $suffixed => $number) {
            $ordinalized = ordinalize($number);
            $this->assertSame($suffixed, $ordinalized);
        }
    }
}
