<?php

$totalSteps = count($tasks);
$currStep   = 1;

foreach ($tasks as $task) {
    CLI::showProgress($currStep++, $totalSteps);
    $task->run();
}

// Done, so erase it...
CLI::showProgress(false);
