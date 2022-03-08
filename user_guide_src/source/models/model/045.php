<?php

$users = $userModel->where('status', 'active')
    ->orderBy('last_login', 'asc')
    ->findAll();
