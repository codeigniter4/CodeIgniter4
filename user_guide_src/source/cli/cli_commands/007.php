<?php

use CodeIgniter\CLI\CLI;

$length = max(array_map('strlen', array_keys($this->options)));

foreach ($this->options as $option => $description) {
    CLI::write(CLI::color($this->setPad($option, $length, 2, 2), 'green') . $description);
}
/*
 * Output will be:
 *  -n     Set migration namespace
 *  -g     Set database group
 *  --all  Set for all namespaces, will ignore (-n) option
 */
