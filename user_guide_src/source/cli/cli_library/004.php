<?php

use CodeIgniter\CLI\CLI;

$overwrite = CLI::prompt('File exists. Overwrite?', ['y', 'n']);
