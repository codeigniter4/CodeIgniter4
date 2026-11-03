<?php

// Retrieve a Job instance
$job = $model->find(15);

// Make some changes
$job->name = 'Foobar';

// Save the changes
$model->save($job);
