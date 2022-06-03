<?php

$data = 'Some file data';

if (! write_file('./path/to/file.php', $data)) {
    echo 'Unable to write the file';
} else {
    echo 'File written!';
}
