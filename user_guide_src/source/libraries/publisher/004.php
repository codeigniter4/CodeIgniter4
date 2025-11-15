<?php

// Place all files into $destination
$frameworkPublisher->copy();

// Place all files into $destination, overwriting existing files
$frameworkPublisher->copy(true);

// Place files into their relative $destination directories, overwriting and saving the boolean result
$result = $frameworkPublisher->merge(true);
