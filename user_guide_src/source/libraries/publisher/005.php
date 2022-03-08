<?php

use CodeIgniter\CLI\CLI;
use CodeIgniter\Publisher\Publisher;

foreach (Publisher::discover() as $publisher) {
    $result = $publisher->publish();

    if ($result === false) {
        CLI::error(get_class($publisher) . ' failed to publish!', 'red');
    }
}
