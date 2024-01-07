<?php

use CodeIgniter\Config\Factories;

$users = Factories::models('UserModel', ['getShared' => true]);  // Default; will always be the same instance
$other = Factories::models('UserModel', ['getShared' => false]); // Will always create a new instance
