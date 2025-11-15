<?php

namespace App\Models;

use CodeIgniter\Test\Fabricator;
use Faker\Generator;

class UserModel
{
    protected $table = 'users';

    public function fake(Generator &$faker)
    {
        return [
            'first'    => $faker->firstName(),
            'email'    => $faker->email(),
            'group_id' => mt_rand(1, Fabricator::getCount('groups')),
        ];
    }
}
