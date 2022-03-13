<?php

class Myclass
{
    public $title   = 'My Title';
    public $content = 'My Content';
    public $date    = 'My Date';
}

$object = new Myclass();
$builder->where('id', $id);
$builder->update($object);
/*
 * Produces:
 * UPDATE `mytable`
 * SET `title` = '{$title}', `name` = '{$name}', `date` = '{$date}'
 * WHERE id = `$id`
 */
