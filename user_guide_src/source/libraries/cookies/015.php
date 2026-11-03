<?php

service('response')->setCookie('admin_token', 'yes');
service('response')->deleteCookie('login_token');

// using the cookie helper
helper('cookie');
set_cookie('admin_token', 'yes');
delete_cookie('login_token');
