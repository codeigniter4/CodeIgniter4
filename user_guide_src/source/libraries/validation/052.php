<?php

namespace App\Controllers;

use App\Entity\User;
use App\ValueObject\Name;
use DomainException;

class UserController
{
    public function index(): string
    {
        $data = [
            'name'       => new Name('Ivan'),
            'created_at' => '01/01/2000',
        ];

        try {
            $user = new User($data);

            // Hi, Ivan
            return 'Hi, ' . $user->name->value;
        } catch (DomainException $e) {
            echo 'Found errors: ';

            print_r(service('validation')->getErrors());
        }
    }
}
