<?php

$user = new \App\Entities\User();
$user->hasChanged('name'); // false

$user->name = 'Fred';
$user->hasChanged('name'); // true
