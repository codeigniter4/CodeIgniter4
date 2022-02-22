<?php

// Check that an element with class 'notice' exists
$results->seeElement('.notice');
// Check that an element with id 'title' exists
$results->seeElement('#title');
// Verify that an element with id 'title' does NOT exist
$results->dontSeeElement('#title');
