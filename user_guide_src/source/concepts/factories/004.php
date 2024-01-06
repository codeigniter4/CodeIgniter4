<?php

use CodeIgniter\Config\Factories;

$conn  = db_connect('auth');
$users = Factories::models('UserModel', [], $conn);
