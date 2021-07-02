<?php

namespace CodeIgniter\Models;

use Tests\Support\Models\UserModel;

/**
 * @internal
 */
final class CountAllModelTest extends LiveModelTestCase
{
    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1617
     */
    public function testCountAllResultsRespectsSoftDeletes(): void
    {
        $this->createModel(UserModel::class);

        // testSeeder has 4 users....
        $this->assertSame(4, $this->model->countAllResults());

        $this->model->where('name', 'Derek Jones')->delete();
        $this->assertSame(3, $this->model->countAllResults());
    }

    public function testcountAllResultsRecoverTempUseSoftDeletes(): void
    {
        $this->createModel(UserModel::class);
        $this->model->delete(1);
        $this->assertSame(4, $this->model->withDeleted()->countAllResults());
        $this->assertSame(3, $this->model->countAllResults());
    }

    public function testcountAllResultsFalseWithDeletedTrue(): void
    {
        $builder     = $this->loadBuilder('user');
        $expectedSQL = $builder->testMode()->countAllResults();

        $this->createModel(UserModel::class);
        $this->model->delete(1);

        $this->assertSame(4, $this->model->withDeleted()->countAllResults(false));
        $this->assertSame($expectedSQL, (string) $this->db->getLastQuery());
        $this->assertFalse($this->getPrivateProperty($this->model, 'tempUseSoftDeletes'));
        $this->assertSame(4, $this->model->countAllResults());
        $this->assertSame($expectedSQL, (string) $this->db->getLastQuery());
        $this->assertTrue($this->getPrivateProperty($this->model, 'tempUseSoftDeletes'));
    }

    public function testcountAllResultsFalseWithDeletedFalse(): void
    {
        $builder     = $this->loadBuilder('user');
        $expectedSQL = $builder->testMode()->where('user.deleted_at', null)->countAllResults();

        $this->createModel(UserModel::class);
        $this->model->delete(1);

        $this->assertSame(3, $this->model->withDeleted(false)->countAllResults(false));
        $this->assertSame($expectedSQL, (string) $this->db->getLastQuery());
        $this->assertFalse($this->getPrivateProperty($this->model, 'tempUseSoftDeletes'));
        $this->assertSame(3, $this->model->countAllResults());
        $this->assertSame($expectedSQL, (string) $this->db->getLastQuery());
        $this->assertTrue($this->getPrivateProperty($this->model, 'tempUseSoftDeletes'));
    }

    public function testcountAllResultsFalseWithDeletedTrueUseSoftDeletesFalse(): void
    {
        $builder     = $this->loadBuilder('user');
        $expectedSQL = $builder->testMode()->countAllResults();

        $this->createModel(UserModel::class);
        $this->model->delete(1);
        $this->setPrivateProperty($this->model, 'useSoftDeletes', false);

        $this->assertSame(4, $this->model->withDeleted()->countAllResults(false));
        $this->assertSame($expectedSQL, (string) $this->db->getLastQuery());
        $this->assertFalse($this->getPrivateProperty($this->model, 'tempUseSoftDeletes'));
        $this->assertSame(4, $this->model->countAllResults());
        $this->assertSame($expectedSQL, (string) $this->db->getLastQuery());
        $this->assertFalse($this->getPrivateProperty($this->model, 'tempUseSoftDeletes'));
    }

    public function testcountAllResultsFalseWithDeletedFalseUseSoftDeletesFalse(): void
    {
        $builder     = $this->loadBuilder('user');
        $expectedSQL = $builder->testMode()->where('user.deleted_at', null)->countAllResults();

        $this->createModel(UserModel::class);
        $this->model->delete(1);
        $this->setPrivateProperty($this->model, 'useSoftDeletes', false);

        $this->assertSame(3, $this->model->withDeleted(false)->countAllResults(false));
        $this->assertSame($expectedSQL, (string) $this->db->getLastQuery());
        $this->assertFalse($this->getPrivateProperty($this->model, 'tempUseSoftDeletes'));
        $this->assertSame(3, $this->model->countAllResults());
        $this->assertSame($expectedSQL, (string) $this->db->getLastQuery());
        $this->assertFalse($this->getPrivateProperty($this->model, 'tempUseSoftDeletes'));
    }
}
