<?php

class UserModel
{
    public function fake(Generator &$faker)
    {
        return [
            'first'  => $faker->firstName,
            'email'  => $faker->email,
            'phone'  => $faker->phoneNumber,
            'avatar' => Faker\Provider\Image::imageUrl(800, 400),
            'login'  => config('Auth')->allowRemembering ? date('Y-m-d') : null,
        ];
    }
}