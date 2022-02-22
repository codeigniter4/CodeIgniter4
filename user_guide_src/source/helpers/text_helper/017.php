<?php

$disallowed = ['darn', 'shucks', 'golly', 'phooey'];
$string     = word_censor($string, $disallowed, 'Beep!');
