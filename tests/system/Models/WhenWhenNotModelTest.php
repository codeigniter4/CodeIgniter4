<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Models;

use Tests\Support\Models\SecondaryModel;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class WhenWhenNotModelTest extends LiveModelTestCase
{
    public function testWhenWithTrueCondition(): void
    {
        $secondaryData = [
            [
                'key'   => 'foo',
                'value' => 'foobar',
            ],
            [
                'key'   => 'bar',
                'value' => 'foobar',
            ],
            [
                'key'   => 'baz',
                'value' => 'foobaz',
            ],
        ];
        $filter = 'foobar';

        $this->createModel(SecondaryModel::class)->insertBatch($secondaryData);

        $result = $this->model->when($filter, static function ($query, $filter) {
            $query->where('value', $filter);
        })->find();

        $this->assertCount(2, $result);
        $this->assertSame('foo', $result[0]->key);
        $this->assertSame('bar', $result[1]->key);
    }

    public function testWhenWithFalseConditionAndDefaultCallback(): void
    {
        $secondaryData = [
            [
                'key'   => 'foo',
                'value' => 'foobar',
            ],
            [
                'key'   => 'bar',
                'value' => 'foobar',
            ],
            [
                'key'   => 'baz',
                'value' => 'foobaz',
            ],
        ];
        $filter = '';

        $this->createModel(SecondaryModel::class)->insertBatch($secondaryData);

        $result = $this->model->when($filter, static function ($query, $filter) {
            $query->where('value', $filter);
        }, static function ($query) {
            $query->where('value', 'foobar');
        })->find();

        $this->assertCount(2, $result);
        $this->assertSame('foo', $result[0]->key);
        $this->assertSame('bar', $result[1]->key);
    }

    public function testWhenNotWithFalseCondition(): void
    {
        $secondaryData = [
            [
                'key'   => 'foo',
                'value' => 'foobar',
            ],
            [
                'key'   => 'bar',
                'value' => 'foobar',
            ],
            [
                'key'   => 'baz',
                'value' => 'foobaz',
            ],
        ];
        $filter = '';

        $this->createModel(SecondaryModel::class)->insertBatch($secondaryData);

        $result = $this->model->whenNot($filter, static function ($query, $filter) {
            $query->where('value !=', 'foobar');
        })->find();

        $this->assertCount(1, $result);
        $this->assertSame('baz', $result[0]->key);
        $this->assertSame('foobaz', $result[0]->value);
    }

    public function testWhenNotWithTrueConditionAndDefaultCallback(): void
    {
        $secondaryData = [
            [
                'key'   => 'foo',
                'value' => 'foobar',
            ],
            [
                'key'   => 'bar',
                'value' => 'foobar',
            ],
            [
                'key'   => 'baz',
                'value' => 'foobaz',
            ],
        ];
        $filter = 'foobar';

        $this->createModel(SecondaryModel::class)->insertBatch($secondaryData);

        $result = $this->model->whenNot($filter, static function ($query, $filter) {
            $query->where('value !=', 'foobar');
        }, static function ($query) {
            $query->where('value', 'foobar');
        })->find();

        $this->assertCount(2, $result);
        $this->assertSame('foo', $result[0]->key);
        $this->assertSame('bar', $result[1]->key);
    }
}
