<?php

// wait for specified interval, with countdown displayed
CLI::wait($seconds, true);

// show continuation message and wait for input
CLI::wait(0, false);

// wait for specified interval
CLI::wait($seconds, false);
