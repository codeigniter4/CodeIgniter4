<?php

namespace App\Models;

class UserModel
{
    protected $table = 'users';

    public function fake(Generator &$faker)
    {
        return [
            'first'    => $faker->firstName,
            'email'    => $faker->email,
            'group_id' => mt_rand(1, Fabricator::getCount('groups')),
        ];
    }
}
