<?php

$values = [
    'logged_in' => 123,
];

$result = $this->withSession($values)->get('admin');

// Or...

$_SESSION['logged_in'] = 123;

$result = $this->withSession()->get('admin');
