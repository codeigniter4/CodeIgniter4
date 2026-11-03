<?php

// Check that "Hello World" is on the page
if ($results->see('Hello World')) {
    // ...
}

// Check that "Hello World" is within an h1 tag
if ($results->see('Hello World', 'h1')) {
    // ...
}

// Check that "Hello World" is within an element with the "notice" class
if ($results->see('Hello World', '.notice')) {
    // ...
}

// Check that "Hello World" is within an element with id of "title"
if ($results->see('Hello World', '#title')) {
    // ...
}
