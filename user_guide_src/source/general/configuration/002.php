<?php

// Get shared instance with config function
$config = config('Pager');

// Access config class with namespace
$config = config('Config\\Pager');
$config = config(\Config\Pager::class);

// Creating a new object with config function
$config = config('Pager', false);
