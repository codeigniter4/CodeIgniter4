<?php

// You can specify conditions directly.
$model = new \App\Models\UserModel();

$data = [
    'users' => $model->where('ban', 1)->paginate(10),
    'pager' => $model->pager,
];

// You can move the conditions to a separate method.
// Model method
class UserModel extends Model
{
    public function banned()
    {
        $this->builder()->where('ban', 1);

        return $this; // This will allow the call chain to be used.
    }
}

$data = [
    'users' => $model->banned()->paginate(10),
    'pager' => $model->pager,
];
