<?php

$hobbies = CLI::promptByMultipleKeys('Select your hobbies:', ['Playing game', 'Sleep', 'Badminton']);
/*
 * Select your hobbies:
 *   [0]  Playing game
 *   [1]  Sleep
 *   [2]  Badminton
 *
 * You can specify multiple values separated by commas.
 * [0, 1, 2]:
 *
 * if your answer is '0,2', the return is the key and the value of the options :
 * [
 *   [0] => "Playing game",
 *   [2] => "Badminton"
 * ]
 */
