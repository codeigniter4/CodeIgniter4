<?php

$fruit = CLI::promptByKey(['These are your choices:', 'Which would you like?'], [
    'apple'  => 'The red apple',
    'orange' => 'The plump orange',
    'banana' => 'The ripe banana',
]);
/*
 * These are your choices:
 *   [apple]   The red apple
 *   [orange]  The plump orange
 *   [banana]  The ripe banana
 *
 * Which would you like? [apple, orange, banana]:
 */
