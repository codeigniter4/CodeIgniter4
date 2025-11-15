<?php

$str = 'this_string_is_entirely_too_long_and_might_break_my_design.jpg';
echo ellipsize($str, 32, .5);
