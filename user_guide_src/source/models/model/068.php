<?php

$userExists       = $userModel->existsById($userId);
$activeUserExists = $userModel->where('active', 1)->existsById($userId);
