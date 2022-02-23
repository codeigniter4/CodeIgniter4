<?php

$fabricator = new Fabricator('App\Models\UserModel');
$fabricator->setOverrides(['name' => 'Gerry']);
$user = $fabricator->create();
