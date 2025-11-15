<?php

$array = ['title' => $match, 'page1' => $match, 'page2' => $match];
$builder->like($array);
/*
 * WHERE `title` LIKE '%match%' ESCAPE '!'
 *     AND  `page1` LIKE '%match%' ESCAPE '!'
 *     AND  `page2` LIKE '%match%' ESCAPE '!'
 */
