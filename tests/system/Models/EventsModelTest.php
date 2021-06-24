<?php

namespace CodeIgniter\Models;

use CodeIgniter\Database\Exceptions\DataException;
use Tests\Support\Models\EventModel;

/**
 * @internal
 */
final class EventsModelTest extends LiveModelTestCase
{
    /**
     * @var EventModel
     */
    protected $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createModel(EventModel::class);
    }

    public function testInsertEvent(): void
    {
        $data = [
            'name'    => 'Foo',
            'email'   => 'foo@example.com',
            'country' => 'US',
            'deleted' => 0,
        ];

        $this->model->insert($data);
        $this->assertTrue($this->model->hasToken('beforeInsert'));
        $this->assertTrue($this->model->hasToken('afterInsert'));
    }

    public function testUpdateEvent(): void
    {
        $data = [
            'name'    => 'Foo',
            'email'   => 'foo@example.com',
            'country' => 'US',
            'deleted' => 0,
        ];

        $id = $this->model->insert($data);

        $this->model->update($id, $data);
        $this->assertTrue($this->model->hasToken('beforeUpdate'));
        $this->assertTrue($this->model->hasToken('afterUpdate'));
    }

    public function testDeleteEvent(): void
    {
        $this->model->delete(1);
        $this->assertTrue($this->model->hasToken('beforeDelete'));
        $this->assertTrue($this->model->hasToken('afterDelete'));
    }

    public function testFindEvent(): void
    {
        $this->model->find(1);
        $this->assertTrue($this->model->hasToken('beforeFind'));
        $this->assertTrue($this->model->hasToken('afterFind'));
    }

    public function testBeforeFindReturnsData(): void
    {
        $this->model->beforeFindReturnData = true;

        $result = $this->model->find(1);
        $this->assertTrue($this->model->hasToken('beforeFind'));
        $this->assertSame($result, 'foobar');
    }

    public function testBeforeFindReturnDataPreventsAfterFind(): void
    {
        $this->model->beforeFindReturnData = true;
        $this->model->find(1);
        $this->assertFalse($this->model->hasToken('afterFind'));
    }

    public function testFindEventSingletons(): void
    {
        // afterFind
        $this->model->first();
        $this->assertTrue($this->model->eventData['singleton']);

        $this->model->find(1);
        $this->assertTrue($this->model->eventData['singleton']);

        $this->model->find();
        $this->assertFalse($this->model->eventData['singleton']);

        $this->model->findAll();
        $this->assertFalse($this->model->eventData['singleton']);

        // beforeFind
        $this->model->beforeFindReturnData = true;

        $this->model->first();
        $this->assertTrue($this->model->eventData['singleton']);

        $this->model->find(1);
        $this->assertTrue($this->model->eventData['singleton']);

        $this->model->find();
        $this->assertFalse($this->model->eventData['singleton']);

        $this->model->findAll();
        $this->assertFalse($this->model->eventData['singleton']);
    }

    public function testAllowCallbacksFalsePreventsTriggers(): void
    {
        $this->model->allowCallbacks(false)->find(1);
        $this->assertFalse($this->model->hasToken('afterFind'));
    }

    public function testAllowCallbacksTrueFiresTriggers(): void
    {
        $this->setPrivateProperty($this->model, 'allowCallbacks', false);
        $this->model->allowCallbacks(true)->find(1);
        $this->assertTrue($this->model->hasToken('afterFind'));
    }

    public function testAllowCallbacksResetsAfterTrigger(): void
    {
        $this->model->allowCallbacks(false)->find(1);
        $this->model->delete(1);

        $this->assertFalse($this->model->hasToken('afterFind'));
        $this->assertTrue($this->model->hasToken('afterDelete'));
    }

    public function testAllowCallbacksUsesModelProperty(): void
    {
        $this->setPrivateProperty($this->model, 'allowCallbacks', false);
        $this->setPrivateProperty($this->model, 'tempAllowCallbacks', false); // Was already set by the constructor

        $this->model->find(1);
        $this->model->delete(1);

        $this->assertFalse($this->model->hasToken('afterFind'));
        $this->assertFalse($this->model->hasToken('afterDelete'));
    }

    public function testInvalidEventException(): void
    {
        $data = [
            'name'    => 'Foo',
            'email'   => 'foo@example.com',
            'country' => 'US',
            'deleted' => 0,
        ];

        $this->setPrivateProperty($this->model, 'beforeInsert', ['anotherBeforeInsertMethod']);

        $this->expectException(DataException::class);
        $this->expectExceptionMessage('anotherBeforeInsertMethod is not a valid Model Event callback.');
        $this->model->insert($data);
    }
}
