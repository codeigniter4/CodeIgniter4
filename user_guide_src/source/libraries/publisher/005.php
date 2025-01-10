<?php

use CodeIgniter\CLI\CLI;
use CodeIgniter\Publisher\Publisher;

foreach (Publisher::discover() as $publisher) {
    $result = $publisher->publish();

    if ($result === false) {
        CLI::error($publisher::class . ' failed to publish!', 'red');
    }
}
