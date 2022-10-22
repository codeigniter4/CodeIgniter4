<?php

// Check that "Hello World" is on the page
$results->see('Hello World');
// Check that "Hello World" is within an h1 tag
$results->see('Hello World', 'h1');
// Check that "Hello World" is within an element with the "notice" class
$results->see('Hello World', '.notice');
// Check that "Hello World" is within an element with id of "title"
$results->see('Hello World', '#title');
