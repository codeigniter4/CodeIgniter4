<?php

// Check that an element with class 'notice' exists
if ($results->seeElement('.notice')) {
    // ...
}

// Check that an element with id 'title' exists
if ($results->seeElement('#title')) {
    // ...
}

// Verify that an element with id 'title' does NOT exist
if ($results->dontSeeElement('#title')) {
    // ...
}
