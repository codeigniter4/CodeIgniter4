<?php

use CodeIgniter\Cookie\Cookie;

$cookie = new Cookie('login_token', 'admin');
$cookie->getName(); // 'login_token'

$cookie->withName('remember_token');
$cookie->getName(); // 'login_token'

$new = $cookie->withName('remember_token');
$new->getName(); // 'remember_token'
