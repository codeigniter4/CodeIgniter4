<?php

use Config\Services;

Services::response()->setCookie('admin_token', 'yes');
Services::response()->deleteCookie('login_token');

// using the cookie helper
helper('cookie');
set_cookie('admin_token', 'yes');
delete_cookie('login_token');
