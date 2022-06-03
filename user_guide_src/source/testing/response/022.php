<?php

// Check that an input exists named 'user' with the value 'John Snow'
$results->seeInField('user', 'John Snow');
// Check a multi-dimensional input
$results->seeInField('user[name]', 'John Snow');
