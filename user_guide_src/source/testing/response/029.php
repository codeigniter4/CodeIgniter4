<?php

// Verify that an input exists named 'user' with the value 'John Snow'
$results->assertSeeInField('user', 'John Snow');

// Verify a multi-dimensional input
$results->assertSeeInField('user[name]', 'John Snow');
