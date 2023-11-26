<?php

class Myclass
{
    public $email   = 'ahmadinejad@example.com';
    public $name    = 'Ahmadinejad';
    public $country = 'Iran';
}

$object = new Myclass();
$builder->upsert($object);
