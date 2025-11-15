<?php

$model = model('SomeModel');

$now = \CodeIgniter\I18n\Time::now();

// The following code passes the microseconds to Query Builder.
$model->where('my_dt_field', $now->format('Y-m-d H:i:s.u'))->findAll();
// Generates: SELECT * FROM `my_table` WHERE `my_dt_field` = '2024-07-28 18:57:58.900326'

// But the following code loses the microseconds.
$model->where('my_dt_field', $now)->findAll();
// Generates: SELECT * FROM `my_table` WHERE `my_dt_field` = '2024-07-28 18:57:58'
