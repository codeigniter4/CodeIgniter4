<?php

// Verify that "Hello World" does NOT exist on the page
$results->assertDontSee('Hello World');

// Verify that "Hello World" does NOT exist within any h1 tag
$results->assertDontSee('Hello World', 'h1');
