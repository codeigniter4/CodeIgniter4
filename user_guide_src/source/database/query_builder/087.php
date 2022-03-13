<?php

class Myclass
{
    public $title   = 'My Title';
    public $content = 'My Content';
    public $date    = 'My Date';
}

$object = new Myclass();
$builder->set($object);
$builder->insert();
