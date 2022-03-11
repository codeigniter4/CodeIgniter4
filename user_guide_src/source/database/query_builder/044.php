<?php

$builder->like('title', 'match');
$builder->orNotLike('body', 'match');
// WHERE `title` LIKE '%match% OR  `body` NOT LIKE '%match%' ESCAPE '!'
