<?php

$file = '/etc/php5/apache2/php.ini';
echo set_realpath($file); // Prints '/etc/php5/apache2/php.ini'

$non_existent_file = '/path/to/non-exist-file.txt';
echo set_realpath($non_existent_file, true);    // Shows an error, as the path cannot be resolved
echo set_realpath($non_existent_file, false);   // Prints '/path/to/non-exist-file.txt'

$directory = '/etc/php5';
echo set_realpath($directory);  // Prints '/etc/php5/'

$non_existent_directory = '/path/to/nowhere';
echo set_realpath($non_existent_directory, true);   // Shows an error, as the path cannot be resolved
echo set_realpath($non_existent_directory, false);  // Prints '/path/to/nowhere'
