<?php

use CodeIgniter\Config\Factories;

$users = Factories::models('Blog\Models\UserModel', ['preferApp' => false]);
