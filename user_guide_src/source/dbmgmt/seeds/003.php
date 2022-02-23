<?php

public function run()
{
    $this->call('UserSeeder');
    $this->call('My\Database\Seeds\CountrySeeder');
}
