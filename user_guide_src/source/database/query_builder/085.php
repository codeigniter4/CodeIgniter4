<?php

$builder->set('field', 'field+1', false);
$builder->where('id', 2);
$builder->update();
// gives UPDATE mytable SET field = field+1 WHERE `id` = 2

$builder->set('field', 'field+1');
$builder->where('id', 2);
$builder->update();
// gives UPDATE `mytable` SET `field` = 'field+1' WHERE `id` = 2
