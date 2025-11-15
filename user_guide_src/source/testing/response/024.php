<?php

// Verify that "Hello World" is on the page
$result->assertSee('Hello World');

// Verify that "Hello World" is within an h1 tag
$result->assertSee('Hello World', 'h1');

// Verify that "Hello World" is within an element with the "notice" class
$result->assertSee('Hello World', '.notice');

// Verify that "Hello World" is within an element with id of "title"
$result->assertSee('Hello World', '#title');
