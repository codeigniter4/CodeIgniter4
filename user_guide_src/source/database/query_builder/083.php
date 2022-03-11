<?php

$builder->set('name', $name);
$builder->insert();
// Produces: INSERT INTO mytable (`name`) VALUES ('{$name}')
