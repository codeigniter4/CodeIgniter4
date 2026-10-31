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

namespace CodeIgniter\Models;

use CodeIgniter\Database\Exceptions\DataException;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Models\UserModel;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class ReplaceModelTest extends LiveModelTestCase
{
    public function testReplaceRespectsUseTimestamps(): void
    {
        $this->createModel(UserModel::class);

        $data = [
            'name'    => 'Amanda Holmes',
            'email'   => 'amanda@holmes.com',
            'country' => 'US',
        ];

        $id = $this->model->insert($data);

        $data['id']      = $id;
        $data['country'] = 'UK';

        $sql = $this->model->replace($data, true);
        $this->assertStringNotContainsString('created_at', (string) $sql);
        $this->assertStringNotContainsString('updated_at', (string) $sql);

        $this->model = $this->createModel(UserModel::class);
        $this->setPrivateProperty($this->model, 'useTimestamps', true);
        $sql = $this->model->replace($data, true);
        $this->assertStringContainsString('created_at', (string) $sql);
        $this->assertStringContainsString('updated_at', (string) $sql);
    }

    public function testReplaceProtectsFields(): void
    {
        $this->createModel(UserModel::class);

        $data = [
            'name'        => 'Amanda Holmes',
            'email'       => 'amanda@holmes.com',
            'country'     => 'US',
            'not_allowed' => 'hack',
        ];

        $sql = $this->model->replace($data, true);
        $this->assertStringNotContainsString('not_allowed', (string) $sql);
        $this->assertStringContainsString('email', (string) $sql);
    }

    public function testReplacePreservesPrimaryKeyEvenIfNotInAllowedFields(): void
    {
        $this->createModel(UserModel::class);

        $data = [
            'id'          => 1,
            'name'        => 'Amanda Holmes',
            'email'       => 'amanda@holmes.com',
            'country'     => 'US',
            'not_allowed' => 'hack',
        ];

        $sql = $this->model->replace($data, true);
        $this->assertStringContainsString('id', (string) $sql);
        $this->assertStringNotContainsString('not_allowed', (string) $sql);
    }

    public function testReplaceWithProtectFalseAllowsAllFields(): void
    {
        $this->createModel(UserModel::class);

        $data = [
            'name'        => 'Amanda Holmes',
            'email'       => 'amanda@holmes.com',
            'country'     => 'US',
            'not_allowed' => 'hack',
        ];

        $sql = $this->model->protect(false)->replace($data, true);
        $this->assertStringContainsString('not_allowed', (string) $sql);
    }

    public function testReplaceThrowsDataExceptionWhenNoAllowedFields(): void
    {
        $this->createModel(UserModel::class);
        $this->setPrivateProperty($this->model, 'allowedFields', []);

        $this->expectException(DataException::class);
        $this->expectExceptionMessage('Allowed fields must be specified for model: "Tests\Support\Models\UserModel"');

        $this->model->replace(['id' => 1, 'name' => 'Amanda']);
    }

    public function testReplaceThrowsDataExceptionWhenDatasetEmpty(): void
    {
        $this->createModel(UserModel::class);

        $this->expectException(DataException::class);
        $this->expectExceptionMessage('There is no data to replace.');

        $this->model->replace([]);
    }

    public function testReplaceThrowsDataExceptionWhenAllFieldsProtected(): void
    {
        $this->createModel(UserModel::class);

        $this->expectException(DataException::class);
        $this->expectExceptionMessage('There is no data to replace.');

        $this->model->replace(['not_allowed' => 'hack']);
    }

    public function testReplaceLiveExecutionWithUnallowedFields(): void
    {
        $this->createModel(UserModel::class);

        $data = [
            'name'    => 'Amanda Holmes',
            'email'   => 'amanda@holmes.com',
            'country' => 'US',
        ];

        $id = $this->model->insert($data);

        $replaceData = [
            'id'          => $id,
            'name'        => 'Amanda Updated',
            'email'       => 'amanda@holmes.com',
            'country'     => 'UK',
            'not_allowed' => 'ignored',
        ];

        $result = $this->model->replace($replaceData);
        $this->assertNotFalse($result);

        $this->seeInDatabase('user', [
            'id'      => $id,
            'name'    => 'Amanda Updated',
            'email'   => 'amanda@holmes.com',
            'country' => 'UK',
        ]);
    }
}
