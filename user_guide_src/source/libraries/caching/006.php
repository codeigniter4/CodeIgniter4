<?php

$cache->deleteMatching('prefix_*'); // deletes all items of which keys start with "prefix_"
$cache->deleteMatching('*_suffix'); // deletes all items of which keys end with "_suffix"
