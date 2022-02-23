<?php

$validation->setRule('username', 'Username', 'required|min_length[3]');
$validation->setRule('password', 'Password', ['required', 'min_length[8]', 'alpha_numeric_punct']);
