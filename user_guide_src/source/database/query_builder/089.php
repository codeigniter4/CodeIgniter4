<?php

use App\Libraries\MyClass;

$object = new MyClass();
$builder->where('id', $id);
$builder->update($object);
/*
 * Produces:
 * UPDATE `mytable`
 * SET `title` = '{$title}', `content` = '{$content}', `date` = '{$date}'
 * WHERE id = `$id`
 */
