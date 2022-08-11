<?php

class Myclass
{
    public $email   = 'ahmadinejad@world.com';
    public $name    = 'Ahmadinejad';
    public $country = 'Iran';
}

$object = new Myclass();
$builder->upsert($object);
