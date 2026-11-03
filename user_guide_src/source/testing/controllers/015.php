<?php

// Make sure users are logged in before checking their account
$this->assertFilter('users/account', 'before', 'login');
