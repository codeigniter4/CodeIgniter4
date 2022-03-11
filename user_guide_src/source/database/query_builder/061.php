<?php

$builder->havingLike('title', 'match', 'before'); // Produces: HAVING `title` LIKE '%match' ESCAPE '!'
$builder->havingLike('title', 'match', 'after');  // Produces: HAVING `title` LIKE 'match%' ESCAPE '!'
$builder->havingLike('title', 'match', 'both');   // Produces: HAVING `title` LIKE '%match%' ESCAPE '!'
