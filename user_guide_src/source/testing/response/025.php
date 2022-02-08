<?php

// Checks that "Hello World" does NOT exist on the page
$results->dontSee('Hello World');
// Checks that "Hello World" does NOT exist within any h1 tag
$results->dontSee('Hello World', 'h1');
