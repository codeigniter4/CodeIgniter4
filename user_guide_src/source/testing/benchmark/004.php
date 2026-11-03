<?php

$timers = $benchmark->getTimers();
/*
 * Produces:
 * [
 *     'render view'  => [
 *         'start'    => 1234567890,
 *         'end'      => 1345678920,
 *         'duration' => 15.4315, // number of seconds
 *     ]
 * ]
 */
