<?php

// Create a new class manually
$userModel = new \App\Models\UserModel();

// Create a new class with the model function
$userModel = model('App\Models\UserModel', false);

// Create a shared instance of the model
$userModel = model('App\Models\UserModel');

// Create shared instance with a supplied database connection
// When no namespace is given, it will search through all namespaces
// the system knows about and attempt to located the UserModel class.
$db        = db_connect('custom');
$userModel = model('UserModel', true, $db);
