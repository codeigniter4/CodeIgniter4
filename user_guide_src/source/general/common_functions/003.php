<?php

// Get an instance
$timer = timer();

// Set timer start and stop points
timer('controller_loading');    // Will start the timer
// ...
timer('controller_loading');    // Will stop the running timer
