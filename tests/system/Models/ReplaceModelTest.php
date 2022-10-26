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

use Tests\Support\Models\UserModel;

/**
 * @group DatabaseLive
 *
 * @internal
 */
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
        $this->assertStringNotContainsString('updated_at', $sql);

        $this->model = $this->createModel(UserModel::class);
        $this->setPrivateProperty($this->model, 'useTimestamps', true);
        $sql = $this->model->replace($data, true);
        $this->assertStringContainsString('updated_at', $sql);
    }
}
