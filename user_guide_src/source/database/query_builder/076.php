<?php

class Myclass
{
    public $title   = 'My Title';
    public $content = 'My Content';
    public $date    = 'My Date';
}

$object = new Myclass();
$builder->insert($object);
// Produces: INSERT INTO mytable (title, content, date) VALUES ('My Title', 'My Content', 'My Date')
