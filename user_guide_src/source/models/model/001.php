<?php

// Create a new class manually.
$userModel = new \App\Models\UserModel();

// Create a shared instance of the model.
$userModel = model('UserModel');
// or
$userModel = model('App\Models\UserModel');
// or
$userModel = model(App\Models\UserModel::class);

// Create a new class with the model() function.
$userModel = model('UserModel', false);

// Create shared instance with a supplied database connection.
// When no namespace is given, it will search through all namespaces
// the system knows about and attempts to locate the UserModel class.
$db        = db_connect('custom');
$userModel = model('UserModel', true, $db);
