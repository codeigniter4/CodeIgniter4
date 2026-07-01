<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';

    protected $allowedFields = ['secret_note'];

    protected array $casts = [
        'secret_note' => 'encrypted',
    ];
}

$userModel = model(UserModel::class);

$id = $userModel->insert([
    'secret_note' => 'Internal billing note',
]);

$user = $userModel->find($id);

echo $user['secret_note']; // Internal billing note
