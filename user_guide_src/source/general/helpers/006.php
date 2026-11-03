<?php

// any_in_array() is not in the Array Helper, so it defines a new function
function any_in_array($needle, $haystack)
{
    $needle = is_array($needle) ? $needle : [$needle];

    foreach ($needle as $item) {
        if (in_array($item, $haystack, true)) {
            return true;
        }
    }

    return false;
}

// random_element() is included in Array Helper, so it overrides the native function
function random_element($array)
{
    shuffle($array);

    return array_pop($array);
}
