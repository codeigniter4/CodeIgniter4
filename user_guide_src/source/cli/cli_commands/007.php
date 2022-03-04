<?php

$pad = $this->getPad($this->options, 6);

foreach ($this->options as $option => $description) {
    CLI::write($tab . CLI::color(str_pad($option, $pad), 'green') . $description, 'yellow');
}
/*
 * Output will be:
 * -n        Set migration namespace
 * -r        override file
 */
