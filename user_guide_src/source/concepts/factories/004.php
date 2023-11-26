<?php

$conn  = db_connect('auth');
$users = Factories::models('UserModel', [], $conn);
