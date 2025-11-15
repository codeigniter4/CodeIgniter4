<?php

// Check that an input exists named 'user' with the value 'John Snow'
if ($results->seeInField('user', 'John Snow')) {
    // ...
}

// Check a multi-dimensional input
if ($results->seeInField('user[name]', 'John Snow')) {
    // ...
}
