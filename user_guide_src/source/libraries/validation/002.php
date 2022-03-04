<?php

if (! $this->validate([
    'username' => 'required',
    'password' => 'required|min_length[10]',
    'passconf' => 'required|matches[password]',
    'email' => 'required|valid_email',
])) {
    // ...
}
