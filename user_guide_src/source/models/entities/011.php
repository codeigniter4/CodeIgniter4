<?php

$user = new \App\Entities\User();

// Converted to Time instance
$user->created_at = 'April 15, 2017 10:30:00';

// Can now use any Time methods:
echo $user->created_at->humanize();
echo $user->created_at->setTimezone('Europe/London')->toDateString();
