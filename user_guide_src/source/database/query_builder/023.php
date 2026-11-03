<?php

$builder->where('name !=', $name);
$builder->where('id <', $id);
// Produces: WHERE name != 'Joe' AND id < 45
