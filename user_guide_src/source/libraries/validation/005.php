<?php

$validation->setRule('username', 'Username', 'required|max_length[30]|min_length[3]');
$validation->setRule('password', 'Password', ['required', 'max_length[255]', 'min_length[8]', 'alpha_numeric_punct']);
