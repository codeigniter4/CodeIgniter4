<?php

$builder->havingLike('title', 'match');
$builder->orHavingLike('body', $match);
// HAVING `title` LIKE '%match%' ESCAPE '!' OR  `body` LIKE '%match%' ESCAPE '!'
