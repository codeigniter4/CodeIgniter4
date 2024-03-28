<?php

use App\Models\UserModel;
use CodeIgniter\Test\Fabricator;

$fabricator = new Fabricator(UserModel::class);
$fabricator->setUnique('email'); // sets generated emails to be always unique
$fabricator->setOptional('group_id'); // sets group id to be optional, with 50% chance to be `null`
$fabricator->setValid('age', static fn (int $age): bool => $age >= 18); // sets age to be 18 and above only

$users = $fabricator->make(10);
