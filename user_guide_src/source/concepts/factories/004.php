<?php

$conn  = db_connect('AuthDatabase');
$users = Factories::models('UserModel', [], $conn);
